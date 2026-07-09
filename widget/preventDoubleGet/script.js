define(['jquery', 'twigjs', 'lib/components/base/modal'], function($, Twig, Modal) {
const preventDoubleGet = function () {
  const self = this
  const isLeadCard = AMOCRM.widgets.system.area === 'lcard'
  const $body = document.querySelector('body');
  
  const existContacts = () => AMOCRM.data.current_card.linked_forms.form_models.models.map(el => +el.attributes?.ID).filter(el => !!el);
  let allContacts = {};

  const spinner = {
    add: (parent) => {
      $body.classList.add('page-loading');
    },
    remove: (parent) => {
      $body.classList.remove('page-loading');
    }
  }
  const api = {
    contactByPhoneXHR: null,
    getContactsByPhoneAjax: function(phoneValue) {
      (this.contactByPhoneXHR && this.contactByPhoneXHR.readyState != 4) && this.contactByPhoneXHR.abort();
      return new Promise((resolve, reject) => {
        this.contactByPhoneXHR = $.ajax({
          url: `https://${AMOCRM.widgets.system.domain}/private/ajax/search.php?type=contacts&q=${phoneValue}&query_type=phone`,
          method: 'POST',
          data: {},
          success: (msg) => resolve(msg),
          error: (e) => reject(e)
        });
      });
    }
  }
  const inputLoader = {
    add: ($el) => {
      setTimeout(() => {
        $el.classList.add("control--suggest-loading");
        $el.insertAdjacentHTML('beforeEnd', '<span class="control--suggest--loader spinner-icon"></span>');
      }, 0);
    },
    remove: ($el) => {
      $el.classList.contains("control--suggest-loading") && $el.classList.remove("control--suggest-loading");
      $el.querySelector('span.control--suggest--loader') && $el.querySelector('span.control--suggest--loader').remove();
    }
  }
  const delifyFn = (fn, delay) => {
      let timerId
      return e => {
          if(timerId) clearTimeout(timerId);
          timerId = setTimeout(() => fn(e), delay);
      }
  }
  const linkContact = (contactId, phones) => {
    const $phoneInput = $('[data-params="type=contacts&q=#q#&query_type=phone"]');
    const t = {
      callback: (e) => {
        AMOCRM.data.current_card.linked_forms.cancelAddForm({
          currentTarget: $phoneInput.closest(".linked-form__fields").children(".js-linked-cancel")
        }, e)
        AMOCRM.data.current_card.setNeedReload();
        self.modal.printAlert('Контакт успешно добавлен в сделку.')
      },
      company_id: "",
      id: String(contactId),
      name: phones
    };
    AMOCRM.data.current_card.linkElement($phoneInput, t);
  }
  const linkingContactNative = async (e) => {
    const contactId = e.target.closest('.prevent-doubles-control--suggest--list--item').dataset.contactId;
    const phones = e.target.closest('.prevent-doubles-control--suggest--list--item').querySelector('.suggest-item-pei').innerHTML.trim();
    const $phoneInput = $('[data-params="type=contacts&q=#q#&query_type=phone"]');
    const t = {
      callback: (e) => {
        AMOCRM.data.current_card.linked_forms.cancelAddForm({
          currentTarget: $phoneInput.closest(".linked-form__fields").children(".js-linked-cancel")
        }, e)
        AMOCRM.data.current_card.setNeedReload();
        self.modal.printAlert('Контакт успешно добавлен в сделку.')
      },
      company_id: "",
      id: String(contactId),
      name: phones
    };

    $phoneInput.data('value-id', $phoneInput);
    self.skipTelNumber(e.target.closest('.control-wrapper'));
    AMOCRM.data.current_card.linkElement($phoneInput, t);
  }
  const suggestDublicates = async ($target) => {
    const phone = $target.value;
    const $parent = $target.closest('.control-phone');
    const $list = $parent.querySelector('ul');
    const $wrapper = $parent.querySelector('.control-wrapper');
    $list.innerHTML = ' ';
    $list.classList.remove('prevent-doubles-control-suggest-list-opened');

    if(phone.length <= 4) return;
    
    try {
      inputLoader.add($wrapper);
      const response = await api.getContactsByPhoneAjax(phone);
      
      if(response.status !== "ok") return;
      allContacts = JSON.parse(JSON.stringify(response.result));
      const contacts = response.result;
      existContacts().forEach(exContact => contacts[exContact] && delete contacts[exContact]);

      $list.innerHTML = Object.keys(contacts).map(contactId => {
        if(!contacts[contactId].phone) return '';
        return `
          <li data-value-id="${contactId}" data-contact-id="${contactId}" class="prevent-doubles-control--suggest--list--item">
            <span class="prevent-doubles-control--suggest--list--item-inner" title="${contacts[contactId].name}">${contacts[contactId].name}
              <div class="suggest-item-pei">
              ${contacts[contactId].phone?.join(', ')}
              </div>
            </span>
          </li>
        `
      })?.join('');
      $list.classList.remove('control--suggest--list');
      $list.classList.add('prevent-doubles-control-suggest-list-opened');
      $wrapper.style['z-index'] = 20;

      $list.removeEventListener('click', linkingContactNative);
      $list.addEventListener('click', linkingContactNative, {once: true});
      
      document.querySelector('body').addEventListener('click', () => {
        $list.classList.add('control--suggest--list');
        $list.classList.remove('prevent-doubles-control-suggest-list-opened');
        $wrapper.style['z-index'] = undefined;
        $list.innerHTML = ' ';
      }, {once: true})
    } catch (e) {
      if(e.statusText === "abort") return;
      self.modal.printAlert('Что-то пошло не так. Попробуйте повторить попытку.')
      throw new Error(e)
    } finally {
      inputLoader.remove($wrapper);
    }
  }
  const inputHandler = (e) => {
    suggestDublicates(e.target)
  }
  const clickHandler = (e) => {
    const input = e.target.closest('.linked-form__field__value').querySelector('input.control-phone__formatted')
    suggestDublicates(input);
  }
  const delifyInputHandler = delifyFn(inputHandler, 300);

  self.modal = {
    add: async function ({template, css = {}, listeners = () => {}, destroy= () => {}}) {
      return await new Modal({
        class_name: 'preventDoublesModal',
        disable_overlay_click: true,
        init_animation: true,
        can_centrify: true,
        init: function ($modal_body) {
          $modal_body
            .trigger('modal:loaded') // запускает отображение модального окна
            .html(`<div class="">
                    <span id="close_modal" class="modal-body__close">
                      <span class="icon icon-modal-close"></span>
                    </span>
                    ${template}
                  </div>`)
            .css(css)
            .trigger('modal:centrify')
            .append(``);
          
          listeners($modal_body);
        },
        destroy
      })
    },
    printConfirm: function (confirm) {
      const okBtn = self.render({
        ref: '/tmpl/controls/button.twig'
      },
        {
          text: confirm.okBtn,
          id: "addContactsToLeadButton",
          class_name: "button-input_blue"
        })
      const cancelBtn = self.render({
        ref: '/tmpl/controls/cancel_button.twig'
      },
        {
          text: confirm.cancelBtn,
          id: "cancelModalButton"
      })
      this.add({
        template: `<p>${confirm.body}</p>
        <div class="modal-body__actions ">${okBtn} ${cancelBtn}</div>`,
        listeners: confirm.listeners
      })
    },
    printAlert: function (text) {
      const okBtn = self.render({
        ref: '/tmpl/controls/button.twig'
      },
        {
          text: 'Хорошо',
          id: "confirmButton",
      });
      return new Promise(resolve => {
        this.add({
          template: `
          <div class="">
            <span id="close_modal" class="modal-body__close">
              <span class="icon icon-modal-close"></span>
            </span>
            <h2 class="modal-body__caption head_2">${text}</h2>
            <div class="modal-body__actions ">${okBtn}</div>
          </div>
          `,
          css: {'width': '500px'},
          listeners: ($body) => {
            $body[0].querySelector('#confirmButton').addEventListener('click', () => {
              $body[0].querySelector('.icon-modal-close').click();
            })
            $body[0].querySelector('.icon-modal-close').addEventListener('click', resolve)
          }});
      })
    }
  },
  self.sendRequest = function (url, data = {}) {
    return new Promise(function (resolve, reject) {
      $.ajax({
        url: url,
        method: 'POST',
        data,
        success: function (msg) {
          resolve(msg);
        },
        error: function (e) {
          reject(e)
        }
      });
    })
  }

  self.skipTelNumber = (target) => {
      const copyTel = target.closest('.js-control-phone').querySelector('input[data-type="phone"]');
      $(copyTel).val('').change();
      $(target).val('');
  }

  const changeHandler = (e) => {
    const phone = e.target.value.replace(/[^0-9]/g,'');
    const keys = Object.keys(allContacts).filter(id => {
      return allContacts[id].phone.some(number => {
        number = number.replace(/[^0-9]/g,'')
        const phone2 = number.substring(number.length-phone.length, number.length);
        return (phone2 === phone && !existContacts().includes(+id));
      })
    })

    if(keys.length) {
      const contactId = keys[0];
      const contact = allContacts[contactId];
      self.skipTelNumber(e.target.closest('.control-phone').querySelector('.control-wrapper'));

      self.modal.printConfirm({
        okBtn: 'Добавить контакт в сделку',
        cancelBtn: 'Отмена',
        body: `В системе уже есть контакт с номером телефона ${phone}! Добавить имеющийся контакт в сделку?`,
        listeners: () => {
          $('#addContactsToLeadButton').on('click', (e) => {
            document.getElementById('cancelModalButton').click();
            linkContact(contactId, contact.phone.join(', '));
          });
        }
      });
    }
    return;
  }

  const setListeners = (phoneInput) => {
    const editBtn = phoneInput.closest('[data-pei-code="phone"]').querySelector('[data-type="edit"]')
    phoneInput.removeEventListener('input', delifyInputHandler);
    phoneInput.addEventListener('input', delifyInputHandler);
    phoneInput.removeEventListener('change', changeHandler);
    phoneInput.addEventListener('change', changeHandler);
    editBtn.removeEventListener('click', clickHandler);
    editBtn.addEventListener('click', clickHandler);
  }
  self.initialSetListeners = () => {
     const allPhoneFields = document.querySelectorAll('input.control--suggest--input-inline[data-type="phone"]');
     allPhoneFields.forEach(field => {
      const phoneInput = field.closest('.js-control-phone').querySelector('input.control-phone__formatted');
      setListeners(phoneInput);
     })
  }
  const setObserverListeners = (mutationList) => {
    mutationList && mutationList.forEach(mutation => {
      if(mutation.target.classList.contains('linked-form__multiple-container')) {
        mutation.addedNodes.forEach(node => {
          if(!node.classList.contains('linked-form__field')) return;
          const phoneInput = node.querySelector('input.control-phone__formatted');
          setListeners(phoneInput);
        })
      }
    })
  }

  const observer = new MutationObserver(setObserverListeners);

  this.callbacks = {
    render: function () {
      if(!isLeadCard) return true;
      $('head').append(`<link type="text/css" rel="stylesheet" href="https://wg.belkurort.by/widget/preventDoubleGet/styles.css?v=${Date.now()}">`)
      return true;
    },
    init: function () {
      return true;
    },
    bind_actions: function () {
      // Проверяем, что находимся в карточке сделки
      if(!isLeadCard) return true;
      const phoneContainer = document.querySelector('[data-pei-code="phone"]').closest('.linked-form__multiple-container');
      // Начинаем наблюдение за настроенными изменениями целевого элемента
      observer.observe(phoneContainer, {childList: true, subtree: true});
      // Вешаем слушатели событий на имеющиеся поля
      self.initialSetListeners();
      return true;
    },
    settings: function () {
      return true;
    },
    onSave: function () {
      return true;
    },
    destroy: function () {
      observer.disconnect()
    },
  };
  return this;
};
return preventDoubleGet;
});
