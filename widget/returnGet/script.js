define(['jquery', 
'twigjs', 
'lib/components/base/modal'], function($, Twig, Modal) {
const returnGet = function () {
  const self = this
  self.currentUserId = APP.constant('user').id
  self.currentCardId = (AMOCRM.data.current_card) ? AMOCRM.data.current_card.id : 0
  self.currentCardFields = (AMOCRM.data.current_card) ? AMOCRM.data.current_card.model.attributes : []
  self.currentPipline = (AMOCRM.data.current_card) ? AMOCRM.data.current_card.getPipelineId() : 0
  self.tabs = (AMOCRM.data.current_card) ? AMOCRM.data.current_card.tabs : 0
  self.allowedTabsId = ["leads_99671575280542", "leads_93281563203182", "settings"]
  self.refundFields = [305355, 378075, 398360, 398362, 378479, 377103, 371141, 370933, 370935, 393708, 381911, 381913, 398358, 398364, 398366, 398368, 398370, 398678, 371365, 305205, 305203];
  self.returnTab = $('div.js-cf-group-wrapper[data-id="leads_99671575280542"]')
  self.errorMessage = `
  <div class="modal-body__inner modal-body__inner-success js-modal-success" id="error">
    <span id="close_modal" class="modal-body__close">
      <span class="icon icon-modal-close"></span>
    </span>
    <h2 style="color: red; text-align: center;">Произошла ошибка. Попробуйте ещё раз!</h2>
  </div>`
  self.confirmMessage = function (confirm) {
    return `
    <div class="modal-body__inner modal-body__inner-success js-modal-success">
      <span id="close_modal" class="modal-body__close">
        <span class="icon icon-modal-close"></span>
      </span>
      <p class="modal-body__innner__message-success">${confirm.text}</p>
      <div class="modal-body__actions ">${confirm.okBtn} ${confirm.cancelBtn}</div>
    </div>
    `
  }
  self.okBtn = (text, id = "confirm_return_btn") => {
    return self.render({
      ref: '/tmpl/controls/button.twig'
    },
      {
        text,
        id,
        class_name: "button-input_blue"
      })
  }
  self.cancelBtn = self.render({
    ref: '/tmpl/controls/cancel_button.twig'
  },
    {
      text: 'Нет',
      id: "cancel_return_btn"
    })

  self.isAllowUser = usersArray => usersArray.indexOf(self.currentUserId) !== -1
  self.sendRequest = function (request, data = {}) {
    return new Promise(function (resolve, reject) {
      self.crm_post(request, data,
        function (msg) {
          resolve(msg)
        }, 'json')
    })
  }
  self.getLeadsByIds = async function (ids) {
    try {
      const response = await fetch(`https://${AMOCRM.widgets.system.domain}/api/v4/leads?filter[id]=${ids.join()}`, {
        method: 'GET'
      })
      if (response.status == 200) {
        const leads = await response.json()
        return leads._embedded.leads
      } else {
        throw new Error(`Неожиданный ответ сервера: Статус ${response.status}`)
      }
    } catch (error) {
      return error
    }
  }
  self.addModal = function (template, actions = () => true, destroy = () => true) {
    const modal = new Modal({
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
        actions()
      },
      destroy: destroy
    })
  }
  self.addNote = async (notingLeadId, leadIdInNote, text = '') => {
    try {
      let response = await fetch(`https://${AMOCRM.widgets.system.domain}/api/v4/leads/notes`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify([
          {
            "entity_id": Number(notingLeadId),
            "note_type": "common",
            "params": {
              "text": `${text} https://zdravkyrort.amocrm.ru/leads/detail/${leadIdInNote}`
            }
          }])
      })
      return await response.ok
    } catch (e) {
      return false
    }
  }
  self.linkLeadToLead = async (savingLeadId, linkingLeadId) => {
    if (!!savingLeadId && !!linkingLeadId) {
      try {
        let response = await fetch(`https://${AMOCRM.widgets.system.domain}/api/v4/leads`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify([
            {
              "id": Number(savingLeadId),
              "custom_fields_values": [
                {
                  "field_id": 398102,
                  "values": [
                    {
                      "value": JSON.stringify([Number(linkingLeadId)])
                    }
                  ]
                }]
            }])
        })
        return await response.ok
      } catch (error) {
        return false
      }
    } else {
      return false
    }
  }
  self.linkEntitiesToLead = async (lead_id) => {
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
      return await response.ok
    } catch (error) {
      throw new Error(error)
    }
  }
  self.tabsHider = function () {
    self.tabs._tabs = self.tabs._tabs.filter(el => self.allowedTabsId.includes(el.id));
    self.tabs._sort_order = self.tabs._sort_order.filter(el => self.allowedTabsId.includes(el.id));
    self.tabs.render();
    self.tabs.switchTab(self.tabs._tabs[0].id);
  }
  self.showContact = function () {
    $(".card-fields__linked-block-item").show()
    document.querySelector('.card-fields__linked-block-item').addEventListener("DOMSubtreeModified", event => {
      if ($(".card-fields__linked-block-item").attr('style') === "display: none;") {
        $(".card-fields__linked-block-item").show()
      }
    })
  }
  self.makeLeadCopy = () => {
    return new Promise((resolve, reject) => {
      let data = {}
      Object.assign(data, self.currentCardFields)
      for (let a in data) {
        const aNumber = a.replace(/\D+/g, "")
        if (self.refundFields.indexOf(+aNumber) == -1 && aNumber != "") {
          delete data[a]
        }
      }
      data["lead[NAME]"] = `Возврат: ${data["lead[NAME]"]}`
      data["lead[PIPELINE_ID]"] = "3836187"
      data["lead[STATUS]"] = "36911073"
      data["CFV[398102]"] = JSON.stringify([self.currentCardId])
      data["CFV[398358]"] = new Date().toLocaleDateString()

      $.ajax({
        url: `https://${AMOCRM.widgets.system.domain}/ajax/leads/detail/`,
        method: 'POST',
        data,
        success: (data) => resolve(data)
      })
    })
  }
  self.addTask = function (text, date = new Date(), userId = AMOCRM.data.current_card.main_user, taskType = 1, elementId = AMOCRM.data.current_card.id, elementType = 2) {
    $.ajax({
      url: `https://${AMOCRM.widgets.system.domain}/private/notes/edit2.php`,
      method: 'POST',
      data: {
        "ACTION": "ADD_TASK",
        "BODY": text,
        "MAIN_USER": userId,
        "TASK_TYPE": taskType,
        "END_DATE": date.toLocaleString("ru", { year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric' }).replace(/,/g, ''),
        "DISABLE_WEBHOOKS": "N",
        "ELEMENT_ID": elementId,
        "ELEMENT_TYPE": elementType
      },
      success: function (data) {
      }
    });
  }
  self.addCFV = function (fid, val = []) {
    return {
      "field_id": fid,
      "values": val.map(el => [{ "value": el }])
    }
  }
  self.addRefundAction = async (elementSelector) => {
    // показать модальное окно для подтверждения действия
    self.addModal(
      self.confirmMessage({
        text: 'Вы точно хотите создать возврат по данной сделке?',
        okBtn: self.okBtn("Да"),
        cancelBtn: self.cancelBtn
      }),
      () => {
        $('#confirm_return_btn').click(async () => {
          $('body').addClass('page-loading')
          $('#close_modal').click()
          try {
            const data = await self.makeLeadCopy()
            if (data && data.status == "success" && data.id) { // если запрос прошел
              if (await self.linkEntitiesToLead(data.id) && self.linkLeadToLead(self.currentCardId, data.id)) { // связать новую сделку с сущностями старой и добавить в старую сделку id новой
                await self.addNote(self.currentCardId, data.id, "Создан возврат") // добавляем примечание в текущую сделку
                await self.addNote(data.id, self.currentCardId, "Возврат создан из сделки") // добавляем примечание в новую сделку
                self.addModal(self.confirmMessage({ // показать диалоговое окно о том, что создана сделка и предложить перейти к этой сделке
                  text: 'Новая сделка в разделе возвраты создана. Хотите перейти к сделке на возврат?',
                  okBtn: self.okBtn("Перейти"),
                  cancelBtn: self.cancelBtn
                }), () => {
                  $('#confirm_return_btn').click(() => {
                    $('body').addClass('page-loading')
                    $('#close_modal').click()
                    AMOCRM.router.navigate('/leads/detail/' + data.id, {
                      trigger: !0
                    })
                    $('body').removeClass('page-loading');
                  })
                })
                self.addTask("Плохая новость! Готовим клиенту возврат :-(", undefined, undefined, 1495078)
                self.renderWidget()
              } else {
                self.addModal(self.errorMessage)
              }
            } else {
              self.addModal(self.errorMessage)
            }
          } catch (e) { // если есть ошибка
            self.addModal(self.errorMessage) // вывести диалоговое окно об ошибке
          } finally {
            $('body').removeClass('page-loading');
          }
        })
      }
    )
  }
  self.parseValidJson = function (str) {
    try {
      const result = JSON.parse(str)
      return result
    } catch (e) {
      return false
    }
  }
  self.renderWidget = async function () {
    if ($('#create_return').length) {
      self.returnTab.children().map((_, el) => $(el).show())
      $('#create_return').remove()
      // console.log("Сreate Return Deleted")
    }
    if (!self.currentCardFields["CFV[370933]"] && !self.currentCardFields["CFV[381911]"] && self.currentPipline == 1736272) {
      const linkedLeadsId = self.parseValidJson(self.currentCardFields["CFV[398102]"])
      if (!linkedLeadsId && !linkedLeadsId.length) {
        self.returnTab.children().map((_, el) => $(el).hide())
        await self.returnTab.append(`<div class="linked-form__field">${self.okBtn('Оформить возврат', 'create_return')}</div>`)
        await $('#create_return').click(self.addRefundAction)
        //await self.addRefundAction('#create_return')
      } else {
        const linkedLeads = await self.getLeadsByIds(linkedLeadsId)
      }
    } else if (self.currentPipline == 3836187) {
      self.tabsHider()
      self.showContact()
    }
  }
  this.callbacks = {
    render: function () {
      //Если Админ
      //if(self.isAllowUser([3406348])) {              
      //}
      return true;
    },
    init: function () {
      if (AMOCRM.data.current_entity == "leads") {
        self.renderWidget()
      }
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
};
return returnGet;
})