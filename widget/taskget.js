var taskGet = function () {
    var self = this;
    var widgetname = 'Tasks';
    self.system = self.system();
    self.amouser_id = APP.constant('user').id;
    self.users = [3406348, 3563083, 3449311, 3504832, 3449320, 9567381, 12335137];//Для кого не работает
    self.pipelines = [1736272];//Для каких воронок включаем

    self.events = function (status) {
        let $body = $('body')
        $body.off('mouseover.required_task').off('click.required_task')
        if (!status) {
            $body.on('mouseover.required_task', '.nav__menu-wrapper', () => {
                return !1
            }).on('click.required_task', '.nav__top, .nav__menu-wrapper, .nav__notifications, .js-back-button', () => {
                alert('Поставьте задачу');
                return !1
            })
        }
    },
        self.getInfo = function (leadId) {
            return new Promise((resolve, reject) => {
                let card = AMOCRM.data.current_card,
                // defaults = card.form.model.defaults
                defaults = card.form.model.attributes
                // (AMOCRM.data.is_card && AMOCRM.data.current_card.new_card) //новая карточка
                if (location.pathname !== '/leads/detail/' + leadId) {
                    resolve({
                        pipeline: defaults['lead[PIPELINE_ID]'] || $('[data-pipeline-id]').data('pipelineId'),
                        status: defaults['lead[STATUS]'],
                        responsible: card.main_user,
                        notes: card.notes.notes.models.map(model => model.attributes)
                    })
                    // $.get(`/v3/leads/${leadId}/timeline`, {
                    //     filter: {
                    //         type: [0]
                    //     },
                    //     without: ['contacts', 'companies'],
                    //     page: 1,
                    //     limit: 500
                    // }).then(result => {
                    //     result = result._embedded
                    //     let lead = result.leads[leadId]
                    //     resolve({
                    //         pipeline: lead.pipeline_id,
                    //         status: lead.status_id,
                    //         responsible: lead.responsible_user,
                    //         notes: result.items,
                    //         outside: !0
                    //     })
                    // }, reject)
                } else {
                    resolve({
                        pipeline: defaults['lead[PIPELINE_ID]'] || $('[data-pipeline-id]').data('pipelineId'),
                        status: defaults['lead[STATUS]'],
                        responsible: card.main_user,
                        notes: card.notes.notes.models.map(model => model.attributes)
                    })
                }
            })
        },
        self.clear = function () {
            localStorage.removeItem('required_task')
            localStorage.removeItem('required_task_id')
        }

    self.check = function (leadId) {
        return new Promise((resolve) => {
            self.getInfo(leadId).then(result => {

                /*  if(self.pipelines.includes(result.pipeline) ) { //Для этой воронки не работаем
                        let tasks = result.notes.filter(note => note.object_type.id === 4);
                        tasks.forEach(function(task) { //показываем все
                                $('.feed-note-wrapper-task[data-id="' + task.id + '"] button').show();
                                $('.feed-note-wrapper-task[data-id="' + task.id + '"] textarea').show();
                        });
                        resolve(1);
                    }*/
                //      else{ //Для этой воронки работаем
                let tasks = result.notes.filter(note => note.object_type.id === 4); // console.log(tasks);
                incomplete = tasks.filter(task => !task.status);
                my_incomplete = incomplete.filter(task => task.responsible_user == parseInt(self.amouser_id));
                my_incomplete.forEach(function (task) { //показываем только мои
                    $('.feed-note-wrapper-task[data-id="' + task.id + '"] button').show();
                    $('.feed-note-wrapper-task[data-id="' + task.id + '"] textarea').show();
                });
                //	console.log('result');
                //	console.log(result);
                if (result.status != '142' && result.status != '143') { //&& !AMOCRM.data.current_card.user_rights.is_admin

                    let me_is_responsible = (result.responsible === parseInt(self.amouser_id));
                    let hasIncomplete = ((incomplete.length == 0) && me_is_responsible);
                    if (hasIncomplete) { //нет незавершенных задач вообще, надо поставить задачу
                        //   console.log('надо поставить задачу');
                        localStorage.required_task = !0
                        localStorage.required_task_id = leadId
                        if (result.outside) { //Если не на странице сделки находимся
                            alert('Поставьте задачу');
                            //       console.log('outside');
                            AMOCRM.router.navigate('/leads/detail/' + leadId, {
                                trigger: !0
                            })
                        }
                    } else self.clear()
                    resolve(!hasIncomplete)
                } else resolve(!0)
                //   }
            }, self.clear)
        })
    },
    self.initHideCompleteTask = function() {
        const $tasks = $('.feed-note-wrapper-task');
        $tasks.each((i,t) => {
            const taskId = $(t).data('id');
            const responsible_user_id = APP.data.current_card.notes.notes._byId[taskId].attributes.responsible_user_id;
            if(responsible_user_id != self.amouser_id) {
                $(t).find('.card-task__result-wrapper').css('display', 'none');
            }
        });
    },
    self.waitForObject = function(path, interval = 100, timeout = 10000) {
        return new Promise((resolve, reject) => {
            const startTime = Date.now();
            
            function checkObject() {
                const parts = path.split('.');
                let obj = window;
                
                for (const part of parts) {
                    if (obj === undefined || obj === null) {
                        break;
                    }
                    obj = obj[part];
                }
                
                if (obj !== undefined) {
                    resolve(obj);
                } else if (Date.now() - startTime > timeout) {
                    reject(new Error(`Timeout waiting for ${path}`));
                } else {
                    setTimeout(checkObject, interval);
                }
            }
            
            checkObject();
        });
    },

        this.callbacks = {
            render: function () {
                if (self.users.includes(self.amouser_id)) return true;
                if (AMOCRM.data.current_card && !self.pipelines.includes(AMOCRM.data.current_card.getPipelineId())) return true;

                //this.ajaxPref()
                let card = APP.data.current_card,
                    isLead = card && card.element_type === 2
                if (isLead) {
                    // AMOCRM.data.current_card.$save_btn.off("click.saveButtonTaskWatcher").on("click.saveButtonTaskWatcher", () => {self.check(card.id).then(self.events)})
                    card.$save_btn.off("click.saveButtonTaskWatcher").on("click.saveButtonTaskWatcher", () => { 
                        self.check(card.id).then(self.events) 
                    })
                    self.check(card.id).then(self.events);
                    self.initHideCompleteTask();
                } else {
                    let status = localStorage.required_task,
                        leadId = localStorage.required_task_id
                    if (status && leadId) {
                        self.check(leadId).then(self.events)
                    }
                }
                // let events = ['DOMNodeInserted.taskWatcher', 'DOMNodeRemoved.taskWatcher', 'DOMSubtreeModified.taskWatcher'].join(' ')
                // $('body').off(events).on(events, '.feed-note-wrapper-task', e => {
                    // if (AMOCRM.data.current_entity == "todo-line") {
                    //     if (AMOCRM.data.current_view.existed_items["_" + e.currentTarget.getAttribute("data-id")]) {
                    //         if (AMOCRM.data.current_view.existed_items["_" + e.currentTarget.getAttribute("data-id")].manager.main_user.id == self.amouser_id) {
                    //             console.log("show taskget")
                    //             $('.js-task-result-button,.card-task__result-wrapper__inner__textarea').show()
                    //         }
                    //     }
                    // }

                    // if (e.target === e.currentTarget && isLead && e.handleObj.type === 'DOMNodeInserted') {
                    //     self.check(card.id).then(self.events)
                    // }
                // });
                return true;
            },
            init: function () {
                // выбираем элемент
                var target = document.querySelector('body');
                // создаем экземпляр наблюдателя
                var observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                            if (mutation.addedNodes.length === 0) return;
                            ////////////////////////////////////Функция убирающая модалку "Хотите завершить все незавершенные задачи?/////////////////////////////////////////////////////////
                            let modal = $('body').find('.modal-leave-confirm');
                            if ((modal.length > 0 && modal.find('h2:contains("Хотите завершить все незавершённые задачи?")').length)) {
                                $('span.icon-modal-close').click();
                            }
                            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                            if (self.users.includes(self.amouser_id)) return true;
                            mutation.addedNodes.forEach(async (node) => {
                                if(node.classList?.contains("feed-note-wrapper-task")) {
                                    if(APP.data.current_card?.element_type === 2) {
                                        self.check(APP.data.current_card.id).then(self.events);
                                    }
                                }

                                if( (node.classList?.contains("feed-note-wrapper-task") && node.querySelector(".card-task")) || // для раздела карточки сделки
                                    (node.classList?.contains("js-note") && node.classList?.contains("card-task-wrapper")) // для раздела todo
                                ){
                                    const current_entity = APP.data.current_entity;
                                    if (!["todo-line", "leads"].includes(current_entity)) return;

                                    const result_wrapper = node.querySelector('.card-task__result-wrapper');
                                    if(!result_wrapper) return;
                                    
                                    const taskId = current_entity == "todo-line" 
                                                    ? node.closest(".todo-form").dataset?.id 
                                                    : node.closest(".feed-note-wrapper-task").dataset?.id;
                                    if(!taskId) return;

                                    if(current_entity === "leads") {
                                        await self.waitForObject('APP.data.current_card.notes.notes', 100, 10000);
                                    }

                                    const responsible_user_id = current_entity == "todo-line" 
                                                                    ? APP.data.current_view.existed_items["_" + taskId]?.manager?.main_user?.id 
                                                                    : APP.data.current_card?.notes?.notes?._byId[taskId].attributes.responsible_user_id;
                                    
                                    if(!responsible_user_id) return;
                                    
                                    if (responsible_user_id != self.amouser_id) {
                                        result_wrapper.style.display = 'none';
                                    }
                                };
                            });
                    });
                });
                // настраиваем наблюдатель
                var config = { childList: true, subtree: true,  };
                // передаем элемент и настройки в наблюдатель
                observer.observe(target, config);

                if (AMOCRM.data.current_card && !self.pipelines.includes(AMOCRM.data.current_card.getPipelineId())) return true;
                // Запретить завершать задачи в разделе задач
                if (self.amouser_id === 3449317) {
                    // $('head').append('<style>.card-task__result-wrapper{display: none;}</style>');
                }
                if (!self.users.includes(self.amouser_id) && !(AMOCRM.data.current_card && AMOCRM.data.current_card.element_type == 1)) {
                    // $('head').append('<style>.js-task-result-button,.card-task__result-wrapper__inner__textarea{display: none;}</style>');
                    // $('head').append('<style>.card-task__result-wrapper{display: none;}</style>');
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