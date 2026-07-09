define([
'jquery', 
'twigjs', 
'lib/components/base/modal'], function ($, Twig, Modal) {
  return function() {
    const self = this;
    self.amouser_id = APP.constant('user').id;
    self.isAllowUser = usersArray => usersArray.indexOf(self.amouser_id) !== -1
    self.sendRequest = function (request, data = {}) {
      return new Promise(function (resolve, reject) {
        $.ajax({
          url: request,
          method: 'POST',
          data,
          success: function (msg) {
            const result = JSON.parse(msg)
            resolve(result);
          },
          error: function () {
            alert('Error')
          }
        });

        // self.crm_post(request, data,
        //   function (msg) {
        //     resolve(msg)
        //   }, 'json')
      })
    }
    self.modal = {
      add: async function (template) {
        return await new Modal({
          class_name: 'modal-window',
          disable_overlay_click: true,
          init_animation: true,
          can_centrify: true,
          init: function ($modal_body) {
            $modal_body
              .trigger('modal:loaded') // запускает отображение модального окна
              .html(template)
              .trigger('modal:centrify')
              .append('');
          }
        })
      },
      error: `
      <div class="modal-body__inner modal-body__inner-success js-modal-success" id="error">
        <span id="close_modal" class="modal-body__close">
          <span class="icon icon-modal-close"></span>
        </span>
        <h2 style="color: red; text-align: center;">Произошла ошибка. Попробуйте ещё раз!</h2>
      </div>`,
      confirm: (confirm) => {
        const okBtn = self.render({
          ref: '/tmpl/controls/button.twig'
        },
          {
            text: confirm.okBtn,
            id: "confirmModalButton",
            class_name: "button-input_blue"
          })
        const cancelBtn = self.render({
          ref: '/tmpl/controls/cancel_button.twig'
        },
          {
            text: confirm.cancelBtn,
            id: "cancelModalButton"
          })
        return `
        <div class="modal-body__inner modal-body__inner-success js-modal-success">
          <span id="close_modal" class="modal-body__close">
            <span class="icon icon-modal-close"></span>
          </span>
          <p class="modal-body__innner__message-success">${confirm.text}</p>
          <div class="modal-body__actions ">${okBtn} ${cancelBtn}</div>
        </div>
        `
      },
      printError: function () {
        this.add(this.error)
      },
      printConfirm: function (confirm) {
        this.add(this.confirm(confirm))
      }
    },

      self.copyButton = {
        template: `
      <li class="button-input__context-menu__item  element__print " id="copy_lead">
        <div class="button-input__context-menu__item__inner">
          <span class="button-input__context-menu__item__icon icon-inline">
            <svg xmlns="http://www.w3.org/2000/svg" height="18" viewBox="0 0 24 24" width="18"><path d="M0 0h24v24H0z" fill="none"/><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
          </span>
          <span class="button-input__context-menu__item__text ">
            Создать копию
          </span>
        </div>
      </li>`,
        contextMenuSelector: $('#card_name_holder').find(".button-input__context-menu "),
        add: function () {
          this.contextMenuSelector.prepend(this.template)
          this.clickHandler()
        },
        makeCopyLead: async function (lead) {
          return new Promise((resolve) => {
            const copiedFields = [305089, 313885, 305179, 305323, 305195,
              346135, 346137, 313433, 313921,
              371365, 305333, 377797, 314783,
              324415, 324427, 324451, 324461,
              305301, 324417, 324429, 324453, 324463,
              305303, 324419, 324435, 324455, 324465,
              305305, 324421, 324439, 324457, 324469,
              305307, 324423, 324441, 324459, 324471]
            let data = {}
            copiedFields.map((el) => { data[`CFV[${el}]`] = (lead[`CFV[${el}]`]) ? lead[`CFV[${el}]`] : "" })
            const prefix = (lead["lead[NAME]"].indexOf('Повторный заказ: ') === -1 && lead["lead[NAME]"].indexOf('ПЗ: ') === -1) ? 'ПЗ: ' : '';
            data["lead[NAME]"] = `${prefix}${lead["lead[NAME]"]}`
            data["lead[PRICE]"] = "0"
            data["lead[PIPELINE_ID]"] = "1736272"
            data["lead[lead[MAIN_USER]"] = self.amouser_id.toString()
            //data["lead[STATUS]"] = "26081347"
            data["lead[STATUS]"] = "143"
            data["CFV[305083]"] = "471263"
            data["CFV[305085]"] = "471265"
            data["CFV[305091]"] = "30"
            data["CFV[305093]"] = "30"

            $.ajax({
              url: `https://${AMOCRM.widgets.system.domain}/ajax/leads/detail/`,
              method: 'POST',
              data,
              success: (data) => resolve(data)
            })
          })
        },
        linkEntitiesToLead: async (lead_id) => {
          const links = AMOCRM.data.current_card.linked_forms.form_models.models
            .filter(el => el.element_type == 3 || el.element_type == 1)
            .map(el => {
              return {
                "to_entity_id": Number(el.attributes.ID),
                "to_entity_type": el.element_type == 3 ? "companies" : "contacts"
              }
            })
          try {
            let response = await fetch(`https://${AMOCRM.widgets.system.domain}/api/v4/leads/${lead_id}/link`, {
              method: 'POST',
              body: JSON.stringify(links)
            })
            return await response
          } catch (error) {
            self.modal.printError()
          }
        },
        clickHandler: function () {
          $('#copy_lead').click(async () => {
            await self.modal.printConfirm({ text: `Вы точно хотите создать копию этой сделки?`, okBtn: "Да", cancelBtn: "Нет" })
            $('#confirmModalButton').one('click', async () => {
              $('body').addClass('page-loading')
              $('#cancelModalButton').click()
              const newLead = await self.copyButton.makeCopyLead(AMOCRM.data.current_card.model.attributes)
              if (newLead.status == "success" && newLead.id) {
                const linkedContact = await this.linkEntitiesToLead(newLead.id)
                const guestsResult = await self.sendRequest("https://wg.belkurort.by/widget/makeDoubleGet/makeDoubleCompanyAndGuest.php", {
                  "hash": 'kdlfgoiwrqgjag6a5gra6reg3arg2aer6ga6rg3',
                  "originalLeadId": AMOCRM.data.current_card.id,
                  "copyLeadId": newLead.id
                })
                if (linkedContact.ok && guestsResult.result == "ok") {
                  $('body').removeClass('page-loading');
                  self.modal.printConfirm({ text: `Копия сделки успешно создана. Перейти к новой сделке?`, okBtn: `Да`, cancelBtn: `Нет` })
                  $('#confirmModalButton').one('click', async () => {
                    $('#cancelModalButton').click()
                    $('body').addClass('page-loading')
                    await AMOCRM.router.navigate('/leads/detail/' + newLead.id, { trigger: !0 })
                    $('body').removeClass('page-loading');
                  })
                } else {
                  self.modal.printError()
                  $('body').removeClass('page-loading')
                }
              } else {
                self.modal.printError()
                $('body').removeClass('page-loading')
              }
            })
          })
        }
      }

    this.callbacks = {
      render: function () {
        if (APP.data.current_entity == "leads") {
          //if(self.isAllowUser([3406348])) {
          self.copyButton.add()
          //}
        }
        return true;
      },
      init: function () {
        return true;
      },
      bind_actions: function () {
        return true;
      },
      settings: function () {
        return true;
      },
      onSave: function () {
        alert('Сохранено!');
        return true;
      },
      destroy: function () {
      },

    };
    return this;
  }
});