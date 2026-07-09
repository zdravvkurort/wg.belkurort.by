const diskGet = function () {
  const self = this
  self.system = self.system();
  self.server = `https://diskget.belkurort.by`
  self.currentUserId = APP.constant('user').id;
  self.appToken = `9035wu4goiejkfd0g9iw54'pkogk]w45oasdb[v]obfpdgbf;lbk,xgpkr[gtw[oe5gt09i04etrpko]]`
  self.disk = {
    folder: [],
    getCurrentFolder() {
      return this.folder[this.folder.length - 1]
    },
    async init() {
      self.view.renderWidget()
      try {
        self.view.addLoader()
        await this.changeFolder("")
        self.view.removeLoader()
      } catch (error) {
        self.helpers.error("Ошибка сервера", e)
      }
    },
    async loadFolderData(path) {
      const currentPath = (!this.folder.length) ? "" : this.folder.map(el => el.path).join("/") + `/${path}`
      try {
        const data = await self.helpers.sendRequest(`${self.server}/disk/resources`, "GET",
          {
            objectType: AMOCRM.data.current_entity,
            objectId: AMOCRM.data.current_card.id,
            path: currentPath
          })
        if (data && data._embedded) data._embedded.items = data._embedded.items.map(self.helpers.setFormatFiles)
        if (this.folder.length) data._embedded.items.unshift({ type: "goBack", name: "Назад", resource_id: "goBack" })
        const folder = {
          path,
          content: data
        }
        this.folder.push(folder)
        return folder
      } catch (e) {
        self.helpers.error("Ошибка сервера", e)
      }
    },
    async changeFolder(path) {
      try {
        const folder = await this.loadFolderData(path)
        if ($.isEmptyObject(folder.content) || !folder.content._embedded?.items.length) {
          self.view.clearTable()
          self.view.addMessage("Здесь ещё нет файлов")
          self.view.addButton("Загрузить файлы", () => { document.getElementById('diskGet-input').click() }, false, true)
          self.view.addMessage("или")
          self.view.addButton("Добавить папку", async () => { await self.disk.addNewFolder() }, false, false)
          self.view.addMessage("или")
          self.view.addButton("Обновить", async () => {
            self.view.addLoader()
            await self.disk.refreshFolder()
            self.view.removeLoader()
          }, false, false)
        } else {
          self.view.makeTable(folder.content._embedded?.items).updateTable()
        }
      } catch (e) {
        self.view.clearTable()
        self.view.addMessage("Не удалось загрузить папку")
        self.view.addButton("Попробовать ещё раз", async () => {
          self.view.addLoader()
          await self.disk.changeFolder(path)
          self.view.removeLoader()
        }, true, true)
        self.helpers.error("Не удалось загрузить информацию по папке", e)
      }
    },
    async previousFolder() {
      self.view.addLoader()
      try {
        this.folder.pop()
        await this.refreshFolder()
      } catch (e) {
        self.helpers.error("Не удалось загрузить информацию по папке", e)
      } finally {
        self.view.removeLoader()
      }
    },
    async refreshFolder() {
      try {
        const currentFolder = this.getCurrentFolder()
        this.folder.pop()
        await this.changeFolder(currentFolder.path)
      } catch (e) {
        console.error(e)
      }
    },
    async addNewFolder() {
      let name = prompt('Укажите имя новой папки', "Новая папка")
      if (!name) return
      self.view.addLoader()
      name = name.replace(/[/]/g, '')
      const currentPath = (!this.folder.length) ? "" : this.folder.map(el => el.path).join("/") + `/${name}`
      try {
        const data = await self.helpers.sendRequest(`${self.server}/disk/resources`, "PUT",
          {
            objectType: AMOCRM.data.current_entity,
            objectId: AMOCRM.data.current_card.id,
            path: currentPath
          })
        if (data.status == 201) {
          await this.refreshFolder()
        }
      } catch (e) {
        console.error(e)
      } finally {
        self.view.removeLoader()
      }
    },
    async deleteFile(id) {
      const file = this.getCurrentFolder().content._embedded.items.find(el => el.resource_id == id)
      if (confirm(`Вы действительно хотите удалить ${(file.type == "dir") ? 'папку' : 'файл'} ${file.name}?`)) {
        try {
          self.view.addLoader()
          const data = await self.helpers.sendRequest(`${self.server}/disk/resources`, "DELETE", { path: file.path })
          // if (data.status != 204) alert("Произошла ошибка! Файл не удалён!")
          await self.disk.refreshFolder()
        } catch (e) {
          self.helpers.error("Ошибка сервера", e)
        } finally {
          self.view.removeLoader()
        }
      }
    },
    getItem(id) {
      return this.getCurrentFolder().content._embedded?.items.find(el => el.resource_id == id)
    },
    async downloadFile(id) {
      self.view.addLoader()
      try {
        const file = this.getCurrentFolder().content._embedded.items.find(el => el.resource_id == id)
        const url = new URL(`${self.server}/disk/resources/fileLink`)
        url.search = new URLSearchParams({
          filePath: file.path,
          fileName: file.name,
          fileExt: file.extension,
        }).toString()
        const response = await fetch(url, {
          method: "GET",
          headers: { "Auth": self.appToken },
        })
        const blob = await response.blob()
        const link = document.createElement("a")
        link.href = window.URL.createObjectURL(blob)
        link.download = file.name
        link.click()
      } catch (e) {
        self.helpers.error("Ошибка сервера.", e)
      } finally {
        self.view.removeLoader()
      }
    },
    async renameResource(id) {
      const file = this.getCurrentFolder().content._embedded.items.find(el => el.resource_id == id)
      const fileName = (file.type === "file") ? file.name.slice(0, file.name.lastIndexOf(".")) : file.name
      const extension = (file.type === "file") ? file.extension : ""
      let newName = prompt(`Введите новое название ${(file.type === "file") ? 'файла' : 'папки'}`, fileName)
      if (!newName) return
      newName = newName.replace(/[/]/g, '')
      try {
        self.view.addLoader()
        await self.helpers.sendRequest(`${self.server}/disk/resources/rename`, "POST", { path: file.path, newName, extension })
        await self.helpers.setTimer(500)
        await this.refreshFolder()
      } catch (e) {
        self.helpers.error("Ошибка сервера.", e)
      } finally {
        self.view.removeLoader()
      }
    },
    async moveElement(oldPath, newPath, fileName, fileExt) {
      self.view.addLoader()
      try {
        await self.helpers.sendRequest(`${self.server}/disk/resources/move`, "POST", { oldPath, newPath, fileName, fileExt })
        await self.helpers.setTimer(500)
        await this.refreshFolder()
      } catch (e) {
        self.helpers.error("Ошибка сервера.", e)
      } finally {
        self.view.removeLoader()
      }
    }
  }
  self.view = {
    table: null,
    $widget: null,
    $table: null,
    $newFileButton: null,
    $newFolderButton: null,
    $menu: null,
    folderTemplate: `<div class="diskGet">
                        <div id="folderTable" class="diskGet-table">
                            <div class="folderTableEmpty">
                                <p>Загрузка...</p>
                            </div>
                        </div>
                              <button id="diskGet-button-addFile" class="diskGet-button"> <svg class="diskGet-button-icon" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                            <p class="diskGet-button-text">Загрузить файлы</p>
                        </button>
                        <div id="diskGet-addFiles">
                          <form id="diskGetForm" action="" method="post" enctype="multipart/form-data">
                            <input id="diskGet-input" type="file" name="files" id="file-field" multiple="true" hidden>
                          </form> 
                          </div>
                        <button id="diskGet-button-createFolder" class="diskGet-button"> <span class="diskGet-button-icon"> <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M20 6h-8l-2-2H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-1 8h-3v3h-2v-3h-3v-2h3V9h2v3h3v2z"/></svg> </span>
                            <p class="diskGet-button-text">Создать папку</p>
                        </button>
                        <nav class="context-menu-diskGet">

                        </nav>
                    </div>`,
    clearTable() {
      this.$table.innerHTML = ''
      return this
    },
    addMessage(text) {
      let empty = document.querySelector('.folderTableEmpty')
      if (!empty) {
        empty = document.createElement('div')
        empty.classList.add('folderTableEmpty')
        this.$table.append(empty)
      }
      const p = document.createElement('p')
      p.classList.add('folderTableEmptyText')
      p.insertAdjacentText('beforeend', text)
      empty.appendChild(p)
    },
    addButton(text, func, once = true, blue = false) {
      const id = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
      const class_name = (blue) ? 'button-input_blue diskGetButton' : 'button-input diskGetButton'
      const button = self.render(
        { ref: '/tmpl/controls/button.twig' }, // объект data в данном случае содержит только ссылку на шаблон
        { id, class_name, text })
      document.querySelector(".folderTableEmpty").insertAdjacentHTML('beforeend', button)
      document.getElementById(id).addEventListener("click", func, { once })
    },
    renderWidget() {
      self.render_template({
        caption: {
          class_name: 'diskGet'
        },
        body: `<link type="text/css" rel="stylesheet" href="https://wg.belkurort.by/widget/diskGet/style.css" >`, //разметка
        render: this.folderTemplate
      })
      this.$widget = document.querySelector(".diskGet")
      this.$table = document.getElementById("folderTable")
      this.$newFileButton = document.getElementById("diskGet-button-addFile")
      this.$newFolderButton = document.getElementById("diskGet-button-createFolder")
      this.$menu = document.querySelector('.context-menu-diskGet')
      this.setActions()
      $('.diskGet').parent().css('background-color', 'rgb(248,248,248)')
    },
    makeTable(items) {
      const template = `
            {% for item in items %}

            <div class="diskGet-cell" data-id="{{item.resource_id}}" data-type="{{item.type}}" draggable="{{(item.type == "goBack") ? false : true}}">
                <div class="diskGet-cell-icon"> 
                    {% if item.type == "goBack" %}
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z"/></svg>
                    {% elseif item.type == "dir" %}
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                    {% elseif item.media_type == "image" %} 
                        <img class="diskGet-filetype" src="https://wg.belkurort.by/widget/diskGet/src/img/icon_1_image_x16.png" alt="Изображение">
                    {% elseif item.media_type == "compressed" %} 
                        <img class="diskGet-filetype" src="https://wg.belkurort.by/widget/diskGet/src/img/icon_2_archive_x16.png" alt="Архив">
                    {% elseif item.extension == "doc" or item.extension == "docx" %}
                        <img class="diskGet-filetype" src="https://wg.belkurort.by/widget/diskGet/src/img/vnd.openxmlformats-officedocument.wordprocessingml.document" alt="Word">
                    {% elseif item.extension == "xls" or item.extension == "xlsx" %}
                        <img class="diskGet-filetype" src="https://wg.belkurort.by/widget/diskGet/src/img/vnd.google-apps.spreadsheet" alt="Google Таблицы">              
                    {% elseif item.extension == "pdf" %}
                        <img class="diskGet-filetype" src="https://wg.belkurort.by/widget/diskGet/src/img/pdf.png" alt="PDF">                
                    {% else %}
                        <img class="diskGet-filetype" src="https://wg.belkurort.by/widget/diskGet/src/img/undefined.png" alt="Другой формат">
                    {% endif %}
                </div>
                <div class="diskGet-cell-name"> {{item.name}} </div>
                </div>
            {% endfor %}`
      this.table = self.render({ data: template }, { items })
      return this
    },
    updateTable() {
      this.$table.innerHTML = this.table
      return this
    },
    makeMenu(menuPoints) {
      const ul = document.createElement('ul')
      ul.classList.add("context-menu-diskGet__items")
      const pointsHTML = menuPoints.forEach(point => {
        const li = document.createElement('li')
        li.classList.add("context-menu-diskGet__item")
        li.setAttribute("id", point.id)
        if (point.svg) li.insertAdjacentHTML('afterbegin', `<svg class="diskGet-icon" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="20">${point.svg}</svg>`)
        li.insertAdjacentText('beforeend', point.name)
        ul.appendChild(li)
      })
      return ul
    },
    addMenu(clickPosX, clickPosY, item) {
      // if (item.type === "goBack") return
      this.$menu.innerHTML = ''
      const itemsArray = []
      itemsArray.push({ id: "diskGet-createFolder", name: "Создать папку", svg: `<path d="M0 0h24v24H0V0z" fill="none"></path><path d="M20 6h-8l-2-2H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-1 8h-3v3h-2v-3h-3v-2h3V9h2v3h3v2z"></path>` })
      itemsArray.push({ id: "diskGet-loadFile", name: "Загрузить файл", svg: `<path fill="currentColor" d="M2 12H4V17H20V12H22V17C22 18.11 21.11 19 20 19H4C2.9 19 2 18.11 2 17V12M12 2L6.46 7.46L7.88 8.88L11 5.75V15H13V5.75L16.13 8.88L17.55 7.45L12 2Z" />` })
      itemsArray.push({ id: "diskGet-refreshFolder", name: "Обновить папку", svg: `<path fill="green" d="M17.65,6.35C16.2,4.9 14.21,4 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20C15.73,20 18.84,17.45 19.73,14H17.65C16.83,16.33 14.61,18 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6C13.66,6 15.14,6.69 16.22,7.78L13,11H20V4L17.65,6.35Z" />` })
      if (item.type === "file") itemsArray.push({ id: "diskGet-dowload", name: "Скачать файл", svg: ` <path fill="currentColor" d="M5,20H19V18H5M19,9H15V3H9V9H5L12,16L19,9Z" />` })
      if (["file", "dir"].includes(item.type)) itemsArray.push({ id: "diskGet-rename", name: "Переименовать", svg: `<path fill="currentColor" d="M17,7H22V17H17V19A1,1 0 0,0 18,20H20V22H17.5C16.95,22 16,21.55 16,21C16,21.55 15.05,22 14.5,22H12V20H14A1,1 0 0,0 15,19V5A1,1 0 0,0 14,4H12V2H14.5C15.05,2 16,2.45 16,3C16,2.45 16.95,2 17.5,2H20V4H18A1,1 0 0,0 17,5V7M2,7H13V9H4V15H13V17H2V7M20,15V9H17V15H20Z" />` })
      if (["file", "dir"].includes(item.type)) itemsArray.push({ id: "diskGet-delete", name: "Удалить", svg: `<path fill="red" d="M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19M8,9H16V19H8V9M15.5,4L14.5,3H9.5L8.5,4H5V6H19V4H15.5Z" />` })

      this.$menu.appendChild(this.makeMenu(itemsArray))
      this.$menu.style.display = "block"
      this.$menu.style.left = (this.$table.scrollWidth < clickPosX + this.$menu.scrollWidth) ? this.$table.scrollWidth - this.$menu.scrollWidth + "px" : clickPosX + "px"
      this.$menu.style.top = clickPosY + "px"
      this.$menu.setAttribute("data-id", item.resource_id)
    },
    hideMenu() {
      this.$menu.style.display = "none"
    },
    addFileLoader(fileName) {
      let cell = document.createElement('div')
      cell.classList.add("diskGet-file-loader")
      cell.innerHTML = `Загрузка ${fileName}`
      document.getElementById("folderTable").appendChild(cell)
    },
    addLoader() {
      this.$table.parentElement.classList.add('diskGet-loader')
    },
    removeLoader() {
      this.$table.parentElement.classList.remove('diskGet-loader')
    },
    setActions() {
      document.body.addEventListener("click", () => { this.hideMenu() })
      this.$table.addEventListener("click", async (event) => {
        let idEl = event.target.closest('[data-id]'); // Ближайшего предка с id
        if (!idEl) return; // Если нет data-id ничего не делаем

        const id = idEl.getAttribute("data-id")
        const item = self.disk.getItem(id)

        if (id == "goBack") await self.disk.previousFolder()
        if (item && item.type === "dir") {
          this.addLoader()
          await self.disk.changeFolder(item.name)
          this.removeLoader()
        }
      })
      this.$table.addEventListener("contextmenu", async (event) => {
        event.preventDefault()
        let idEl = event.target.closest('[data-id]'); // Ближайшего предка с id
        if (!idEl) return; // Если нет data-id ничего не делаем
        const id = idEl.getAttribute("data-id")
        const item = self.disk.getItem(id)
        this.addMenu(event.layerX, event.layerY, item)
      })
      let selectedElement = null
      this.$table.addEventListener(`dragstart`, (evt) => {
        evt.target.classList.add(`diskGet-cell-selected`)
        if (evt.target.dataset.type == "goBack") evt.preventDefault()
      })
      this.$table.addEventListener(`dragover`, (evt) => {
        const targetEl = (!evt.target.dataset.id) ? findDataId(evt.target) : evt.target
        if (["dir", "goBack"].includes(targetEl.dataset.type)) evt.preventDefault()
        if (this.selectedElement == null || targetEl.outerHTML != this.selectedElement.outerHTML) {
          this.selectedElement = targetEl
          const skipSelect = document.querySelector('.diskGet-cell-target-element')
          if (skipSelect) skipSelect.classList.remove('diskGet-cell-target-element')
          if (["dir", "goBack"].includes(targetEl.dataset.type)) targetEl.classList.add('diskGet-cell-target-element')
        }

        function findDataId(el) {
          if (el.dataset.id) return el
          return findDataId(el.parentElement)
        }
      })
      this.$table.addEventListener(`dragend`, async (evt) => {
        evt.target.classList.remove(`diskGet-cell-selected`)
        const selectedElement = document.querySelector('.diskGet-cell-target-element')
        if (selectedElement) {
          selectedElement.classList.remove('diskGet-cell-target-element')

          const draggableElement = self.disk.getItem(evt.target.dataset.id)
          if (draggableElement) {
            const name = (draggableElement.extension != "") ? draggableElement.name.slice(0, draggableElement.name.lastIndexOf('.')) : draggableElement.name

            if (["dir"].includes(this.selectedElement.dataset.type)) {
              const targetFolder = self.disk.getItem(this.selectedElement.dataset.id)
              if (targetFolder) await self.disk.moveElement(draggableElement.path, targetFolder.path, name, draggableElement.extension)
            }

            if (["goBack"].includes(this.selectedElement.dataset.type)) {
              const targetFolder = self.disk.getCurrentFolder().content.path.split("/").slice(0, -1).join("/")
              if (targetFolder) await self.disk.moveElement(draggableElement.path, targetFolder, name, draggableElement.extension)
            }

          }
        }

        this.selectedElement = null
      })
      this.$menu.addEventListener("click", async (event) => {
        const actionId = event.target.closest('.context-menu-diskGet__item').id
        const elementId = event.target.closest('[data-id]').dataset.id
        if (!elementId || !actionId) return
        if (actionId === "diskGet-createFolder") self.disk.addNewFolder()
        if (actionId === "diskGet-loadFile") document.getElementById('diskGet-input').click()
        if (actionId === "diskGet-dowload") self.disk.downloadFile(elementId)
        if (actionId === "diskGet-rename") self.disk.renameResource(elementId)
        if (actionId === "diskGet-delete") self.disk.deleteFile(elementId)
        if (actionId === "diskGet-refreshFolder") {
          self.view.addLoader()
          await self.disk.refreshFolder()
          self.view.removeLoader()
        }
      })
      this.$newFileButton.addEventListener("click", function () {
        document.getElementById('diskGet-input').click()
      })
      $('#diskGet-input').on('change', async function () {
        try {
          const currentPath = (!self.disk.folder.length) ? "" : self.disk.folder.map(el => el.path).join("/")
          const promiseArray = Array.from(this.files).map(async (file) => {
            if (document.querySelector('.folderTableEmpty')) document.querySelector('.folderTableEmpty').remove()
            self.view.addFileLoader(file.name)
            const urlForLoad = await fetch(`${self.server}/disk/resources/loadLinks`, {
              method: "POST",
              headers: {
                "Auth": self.appToken,
                'Content-Type': 'application/json;charset=utf-8'
              },
              body: JSON.stringify({
                "objectType": AMOCRM.data.current_entity,
                "objectId": AMOCRM.data.current_card.id,
                "path": currentPath,
                "fileName": file.name
              })
            })
            const link = await urlForLoad.json()
            let sendFile = await fetch(link.href, {
              method: "PUT",
              body: file
            })
          })
          await Promise.all(promiseArray)
          const currentFolder = self.disk.folder[self.disk.folder.length - 1]
          self.disk.folder.pop()
          const folder = await self.disk.loadFolderData(currentFolder.path)
          self.view.makeTable(folder.content._embedded?.items).updateTable()
          this.value = null
        } catch (e) {
          self.helpers.error("Ошибка сервера", e)
        }
      })
      this.$newFolderButton.addEventListener("click", async function () {
        await self.disk.addNewFolder()
      })
    }
  }
  self.helpers = {
    error(errorText, e) {
      console.log(errorText, e)
      //alert(errorText)
    },
    async sendRequest(url, type = "GET", data = {}) {
      return new Promise((resolve, reject) => {
        $.ajax({
          type,
          url,
          dataType: 'json',
          headers: {
            "Auth": self.appToken
          },
          data,
        }).done(function (response) {
          resolve(response)
        }).error((error) => {
          reject(error)
        })
      })
    },
    setFormatFiles: function (el) {
      el.extension = (el.type !== "dir") ? /[^.]+$/.exec(el.name)[0] : ''
      return el
    },
    setTimer: function (time) {
      return new Promise(resolve => {
        setTimeout(() => {
          resolve()
        }, time)
      })
    },
  }
  // self.stopSendFiles = {
  //   observers: [],
  //   init: function() {
  //     // if (![3406348].includes(self.currentUserId)) return;
  //     this.onFeedComposeSwitch();
  //     this.eventListenerHandler();
  //   },
  //   onFeedComposeSwitch: function() {
  //     const feedComposeElement = document.querySelector('div.feed-compose__inner');
  //     if(!feedComposeElement) return;
  //     const observer = new MutationObserver(mutations => {
  //       mutations.forEach(mutation => {
  //         const chatElement = Object.values(mutation.addedNodes).find(node => node.classList.contains('feed-compose_amojo'));
  //         if(!chatElement) return;
  //         this.eventListenerHandler();
  //       })
  //     });
  //     observer.observe(feedComposeElement, {
  //       childList: true
  //     })
  //     this.observers.push(observer);
  //   },
  //   onChangeFeedUser: function(chatElement, attachWrapper) {
  //     const changeChatObserver = new MutationObserver(mutations => {
  //       mutations.forEach(mutation => {
  //         if(!this.isWazzup(chatElement)) return;
  //         if(!attachWrapper.childNodes.length) return;
  //         attachWrapper.childNodes.forEach(this.removeAddedFiles);
  //       })
  //     })
  //     changeChatObserver.observe(chatElement.querySelector('.feed-compose__talk-id'), {childList: true});
  //     this.observers.push(changeChatObserver);
  //   },
  //   onAddFiles: function(chatElement, attachWrapper) {
  //     const observer = new MutationObserver(mutations => {
  //       if(!this.isWazzup(chatElement)) return;
  //       mutations.forEach(mutation => {
  //         mutation.addedNodes.forEach(this.removeAddedFiles)
  //       });
  //     })
  //     observer.observe(attachWrapper, {childList: true});
  //     this.observers.push(observer);
  //   },
  //   onAddFileButtonClick: function() {
  //     $('.js-amojo-attach').on('click', (e) => {
  //       const target = e.target;
  //       const chatWrapper = target.closest('.feed-compose_amojo');
  //       if(!this.isWazzup(chatWrapper)) return;
  //       e.preventDefault();
  //       alert('Пожалуйста, для отправки файлов используйте зелёный виджет Wazzup в правом верхнем углу.');
  //     });
  //   },
  //   eventListenerHandler: function() {
  //     const chatElement = document.querySelector('.js-note.feed-compose_amojo');
  //     const attachWrapper = chatElement.querySelector('.js-attachments');

  //     if(!chatElement) return;
  //     this.onChangeFeedUser(chatElement, attachWrapper);
  //     this.onAddFileButtonClick();
  //     this.onAddFiles(chatElement, attachWrapper);
  //   },
  //   removeAddedFiles: (node) => {
  //     node.querySelector('.js-attach-remove').click();
  //     alert('Пожалуйста, для отправки файлов используйте зелёный виджет Wazzup в правом верхнем углу.');
  //   },
  //   isWazzup: (chatElement) => {
  //     const title = chatElement.querySelector('.feed-compose-user__name').dataset.title;
  //     return ['WhatsappWZ', 'zdravkurort official API', 'ЗДРАВКУРОРТ'].some(el => title.indexOf(el) !== -1);
  //   },
  // }
  this.callbacks = {
    render: async function () {
      if(self.system.area === 'lcard') {
        await self.disk.init();
        // self.stopSendFiles.init();
        return true;
      }
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
      // self.stopSendFiles.observers.forEach(observer => observer.disconnect());
      // document.querySelector('.diskGet').parentElement.remove()
    },

  };
  return this;
};