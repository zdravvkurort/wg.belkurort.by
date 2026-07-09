var app = new Vue({
  el: '#vue',
  data: {
    dops: {
      selected: [],
      variants_usl: []
        },
    allsettings: {
      dates_in_out: ["",""],
      valuta: "RUB",
      sanatorii: ""
    },
    currency: [{
      name: "RUB",
      priceName: "price_rub"
    },
    {
      name: "BYN",
      priceName: "price_byn"
    },
    {
      name: "USD",
      priceName: "price_usd"
    },
    {
      name: "EUR",
      priceName: "price_euro"
    }],
	loading: true,
    editableTabsValue: '1',
    allprice: 0,
    info: "",
    selected_items:[],
    curs_from_nb: [],
    appart_price: [],
    sanatoriums: [],
    pickerOptions: {},
  },
  methods: {
    updatePlace() {
      this.loading = true;
      axios
      .get('https://wg.belkurort.by/api/v1/prices/index.php?appart_id='+this.allsettings.sanatorii)
      .then(response => {
        this.appart_price = response.data
          if (this.selected_items.length == 0) {
            this.handleTabsEdit("", "add")
          }
          this.updatePrices()
          this.dops.selected = []
          this.loading = false
      });
      axios
      .get('https://wg.belkurort.by/api/v1/dops/index.php?appart_id='+this.allsettings.sanatorii)
      .then(response => {
        this.dops.variants_usl = response.data
        this.loading = false
      });
    },
    updatePrices() {
      this.setPrices()
      this.setPriceDop()
      this.sumprice()
    },
    updateCategory(index) {
        this.selected_items[index].main_place = this.getAppartSetting(this.selected_items[index].type_appart).main_place
        this.selected_items[index].dop_place = this.getAppartSetting(this.selected_items[index].type_appart).dop_place
        this.selected_items[index].guests = []
        this.editGuests(index, "check")
		this.updatePrices()
    },
    getAppartSetting(nameappart) {
      for (var i = 0; i < this.appart_price.length; i++) {
        if (this.appart_price[i].type_appart == nameappart) {
          return this.appart_price[i]
        }
      }
      this.appart_price
    },
    setPrices() {
      for (var i = 0; i < this.selected_items.length; i++) {
        for (var j = 0; j < this.selected_items[i].guests.length; j++) {
          var price = this.getPrice(this.selected_items[i].guests[j],this.selected_items[i])
          if(price != false) {
            this.selected_items[i].guests[j].price = price[0]
            this.selected_items[i].guests[j].info = price[1]
          } else {
            this.selected_items[i].guests[j].price = false
            this.selected_items[i].guests[j].info = false
          }
        }
      }
      this.sumprice()
    },
    actualCurPrice(priceObj) {
      let valutaName = this.getPriceName(this.allsettings.valuta);
      if(priceObj[valutaName] > 0) {
        return priceObj[valutaName]
      } else {
          for (var i = 0; i < this.currency.length; i++) {
            if(priceObj[this.currency[i].priceName] > 0) {
                if(this.currency[i].name == "BYN") {
                  var kurs = this.findKurs(this.allsettings.valuta);
                  return (priceObj[this.currency[i].priceName] * kurs.Cur_Scale / kurs.Cur_OfficialRate * 1.01).toFixed(2);
                } else if(this.allsettings.valuta == "BYN") {
                  var kurs = this.findKurs(this.currency[i].name);
                  return (priceObj[this.currency[i].priceName] * kurs.Cur_OfficialRate / kurs.Cur_Scale * 1.01).toFixed(2);
                } else {
                  var finalValKurs = this.findKurs(this.allsettings.valuta);
                  var kursValPrice = this.findKurs(this.currency[i].name);
                  return (priceObj[this.currency[i].priceName] * kursValPrice.Cur_OfficialRate/kursValPrice.Cur_Scale * finalValKurs.Cur_Scale / finalValKurs.Cur_OfficialRate * 1.01).toFixed(2)
                }
            }
          }
      }
    },
    getPriceName(valuta) {
      for (var i = 0; i < this.currency.length; i++) {
        if (this.currency[i].name == valuta) {
          return this.currency[i].priceName
        }
      }
    },
    editGuests(i, action) {
      if(action == "add") {
        if(this.selected_items[i].guests.length < (Number(this.selected_items[i].main_place) + Number(this.selected_items[i].dop_place))) {
          for(j=0;j<this.appart_price.length;j++) {
            if(this.appart_price[j].type_appart == this.selected_items[i].type_appart) { //если в ценах есть этот номер
              if(this.appart_price[j].type_book == "Доп место") {//если на этот номер есть цена на доп место
                let countDopPlase = this.selected_items[i].guests.filter(guest => guest.type_book == "Доп место").length;
                if(this.selected_items[i].dop_place > countDopPlase) {//если на этот номер есть доп место, то добавляем гостя
                  this.pushGuest(i,"Доп место")
                //  this.$notify.success({title:"Гость добавлен", message:"Добавили ещё одного гостя"})
                  return true
                }
              }

            }
          }
        } else {
          this.$notify.error({title: "Слишком много людей в номере", message: "Количество человек в номере больше допустимого. Добавьте ещё один номер!"})
        }
      } else if(action == "check") {

		var odnomestn = this.selected_items[i].guests.filter(guest => guest.type_book == "Одноместное размещение");
		var podselenie = this.selected_items[i].guests.filter(guest => guest.type_book == "Подселение");

		if(odnomestn.length != 0) {
			this.selected_items[i].guests = odnomestn
		} else if(podselenie.length != 0) {
			this.selected_items[i].guests = podselenie
		} else {
        let countMainPlace = this.selected_items[i].guests.filter(guest => guest.type_book == "Основное место").length
        for(j=0;j<(this.selected_items[i].main_place - countMainPlace);j++) {
          this.pushGuest(i, "Основное место")
        }
		}
      }
    },
    addGuest(tabId) {
      for (var i = 0; i < this.selected_items.length; i++) {
        if (this.selected_items[i].id == tabId) {
          this.editGuests(i, "add")
    //      this.pushGuest(i)
          this.setPrices()
        }
      }
    },
    pushGuest(i,type_book) {
      let trig = false;
      for (var k = 0; k < this.appart_price.length; k++) {
        if(this.appart_price[k].type_appart == this.selected_items[i].type_appart ||
        this.appart_price[k].type_type_book == type_book) {
          trig = true
        }
      }
      if(trig) {
      //создаём объект нового гостя
      let newGuest = {
              type_book: type_book,
              nationality: this.unique_category.nationality[0],
              old: 18,
              pitanie: this.unique_category.pitanie[0],
              health: this.unique_category.health[0],
              price: "-",
              info: "",
              type_book_access: []
            }

      //добавляем допустимые варианты размещения
      for (var j = 0; j < this.unique_category.type_book.length; j++) {
              newGuest.type_book_access.push({
                                value: this.unique_category.type_book[j],
                                disabled: true
                              })
            }

      //добавляем нового гостя в основной массив
      this.selected_items[i].guests.push(newGuest)
    }
    },
    deleteGuest(tabId,index,row) {
      if(row.type_book != "Основное место") {
      for(i=0; i<this.selected_items.length; i++) {
        if(this.selected_items[i].id == tabId) {
          if(this.selected_items[i].guests.length > Number(this.selected_items[i].main_place)) {
            this.selected_items[i].guests.splice(index,1)
          }
        }
      }
      this.setPrices()
    } else {
      this.$notify.error({title: "Ошибка", message: "Нельзя удалять гостей с основным местом из номера. Удалите с дополнительным!"})
    }
    },
    addDop() {
      this.dops.selected.push({
        id: "",
        count: 1,
        sum: 0
      })
    },
    deleteDop(index,row) {
      this.dops.selected.splice(index,1)
      this.setPriceDop()
    },
    setPriceDop() {
      for (var i = 0; i < this.dops.selected.length; i++) {
        for(var j = 0; j < this.dops.variants_usl.length;j++) {
          if (this.dops.selected[i].id == this.dops.variants_usl[j].id) {
            this.dops.selected[i].sum = this.dops.selected[i].count * this.actualCurPrice(this.dops.variants_usl[j])
          }
        }
      }
      this.sumprice()
    }
    ,
    addNewAppart(id,newTabName) {
      this.selected_items.push({
        id: id,
        name: newTabName,
        type_appart: this.unique_category.type_appart[0],
        main_place: this.getAppartSetting(this.unique_category.type_appart[0]).main_place,
        dop_place: this.getAppartSetting(this.unique_category.type_appart[0]).dop_place,
        guests: []
            });
      //this.pushGuest(this.selected_items.length - 1)
    },
    handleTabsEdit(targetName, action) {
      if (action === 'add') {
        let id = (this.selected_items.length == 0) ? 1 : this.selected_items[this.selected_items.length-1].id + 1;
        let newTabName = id + '';
        this.addNewAppart(id,newTabName);
        this.editableTabsValue = newTabName;
        for(i=0;i<this.selected_items.length;i++) {
          if(this.selected_items[i].id == id) {
            this.editGuests(i, "check")
          }
        }
		this.setPrices()

      }
      if (action === 'remove') {
        let tabs = this.selected_items;
        if(this.selected_items.length > 1) {
        let activeName = this.editableTabsValue;
        if (activeName === targetName) {
          tabs.forEach((tab, index) => {
            if (tab.name === targetName) {
              let nextTab = tabs[index + 1] || tabs[index - 1];
              if (nextTab) {
                activeName = nextTab.name;
                }
              }
          });
        }

        this.editableTabsValue = activeName;
        this.selected_items = tabs.filter(tab => tab.name !== targetName);
                }
        this.updatePrices()
      }
    },
    sumprice() {
      let allPrice = 0;
      for (var i = 0; i < this.selected_items.length; i++) {
        for (var j = 0; j < this.selected_items[i].guests.length; j++) {
          if(this.selected_items[i].guests[j].price == false) {
            this.allprice = "Ошибка"
            return
          }
          allPrice += this.selected_items[i].guests[j].price
        }
      }

      for(x=0;x<this.dops.selected.length;x++) {
        allPrice += this.dops.selected[x].sum
      }

	     this.allprice = Math.ceil(allPrice)

    },
    findKurs(val) {
    for (var z = 0; z < this.curs_from_nb.length; z++) {
      if(this.curs_from_nb[z].Cur_Abbreviation == val) {
        return this.curs_from_nb[z];
      }
    }
  },
  typeBookVarControl() {
    for (var i = 0; i < this.selected_items.length; i++) {
      var countMainPlace = 0;
      for (var j = 0; j < this.selected_items[i].guests.length; j++) {
        if(this.selected_items[i].guests[j].type_book == "Основное место") {
          countMainPlace++
          for (var z = 0; z < this.selected_items[i].guests[j].type_book_access.length; z++) {
            if(this.selected_items[i].guests[j].type_book_access[z].value == "Доп место") {
            this.selected_items[i].guests[j].type_book_access[z].disabled = true
          } else {
            this.selected_items[i].guests[j].type_book_access[z].disabled = false
          }
          }

        } else if(this.selected_items[i].guests[j].type_book == "Доп место") {
          for (var z = 0; z < this.selected_items[i].guests[j].type_book_access.length; z++) {
            if(this.selected_items[i].guests[j].type_book_access[z].value == "Основное место" ||
				this.selected_items[i].guests[j].type_book_access[z].value == "Одноместное размещение" ||
				this.selected_items[i].guests[j].type_book_access[z].value == "Подселение") {
            this.selected_items[i].guests[j].type_book_access[z].disabled = true
          } else {
            this.selected_items[i].guests[j].type_book_access[z].disabled = false
          }
        }
      } else if(this.selected_items[i].guests[j].type_book == "Одноместное размещение" || this.selected_items[i].guests[j].type_book == "Подселение") {
        for (var z = 0; z < this.selected_items[i].guests[j].type_book_access.length; z++) {
          if(this.selected_items[i].guests[j].type_book_access[z].value == "Доп место") {
          this.selected_items[i].guests[j].type_book_access[z].disabled = true
        } else {
          this.selected_items[i].guests[j].type_book_access[z].disabled = false
        }
      }
        this.selected_items[i].guests = [this.selected_items[i].guests[j]]
      }
      }
      if(countMainPlace < this.selected_items[i].main_place) {
        this.editGuests(i, "check")
      }
    }
  },
  filtered_price(type_appart, old, type_book, nationality, pitanie, health) {
    var outArr = [];
    for (var i = 0; i < this.appart_price.length; i++) {
      if(type_appart == this.appart_price[i].type_appart) {//фильтруем категорию номера
        if (this.appart_price[i].old_from <= old && this.appart_price[i].old_to >= old) { //фильтруем возраст
          if (type_book == this.appart_price[i].type_book) { //выбираем тип места
            if (nationality == this.appart_price[i].nationality) { //фильтруем гражданство
              if (pitanie == this.appart_price[i].pitanie) { //фильтруем питание
                if (health == this.appart_price[i].health) {//фильтруем лечение
                  let date_in_select = new Date(this.allsettings.dates_in_out[0]).getTime();
                  let date_in_array = new Date(this.appart_price[i].date_from).getTime();
                  let date_out_select = new Date(this.allsettings.dates_in_out[1]).getTime();
                  let date_out_array = new Date(this.appart_price[i].date_to).getTime();
                  if ((date_in_select <= date_out_array) && (date_out_select >= date_in_array)) { //фильтруем даты
                    outArr.push(this.appart_price[i])
                  }
                }
              }
            }
          }
        }
      }
    }

    outArr = outArr.sort(( a, b ) => {
                                a = new Date(a.date_from).getTime();
                                b = new Date(b.date_from).getTime();
                                if ( a < b ) {
                                  return -1;
                                }
                                if ( a > b ) {
                                  return 1;
                                }
                                return 0;
                              });
      return outArr
  },
  getPrice(obj,appart) {
    this.typeBookVarControl()
    var priceArray = this.filtered_price(appart.type_appart, obj.old, obj.type_book, obj.nationality, obj.pitanie, obj.health);
    if(priceArray == false) {
      this.$notify.error({title: "Нет цен!!!", message: "Нет цен на выбранные даты и с выбранными параметрами"})
      return false
    }
    var info = ""
    var dateIn = new Date(this.allsettings.dates_in_out[0]);
    var dateOut = new Date(this.allsettings.dates_in_out[1]);
    var price = 0;
    var type_tr = { type: "",
                    price: 0,
                    dayorsut: ""};
    var isset_price = {
      in: false,
      mindatein: 0,
      out: false,
      maxdateout: 0
    };

      for(var i = 0; i < priceArray.length; i++) {

        if(i > 0) {
        var a = new Date(priceArray[i].date_from).getTime();
        var b = new Date(priceArray[i-1].date_to).getTime()+86400000;
        if(!(b == a)) {
          this.$notify.error({title: "Нет цен!!!",message: "Внимание! Не задана цена с "+formatDate(new Date(b))+" по "+formatDate(new Date(a)),duration: 0});
          return false
          }
        }

        var dateIn_arr = new Date(priceArray[i].date_from).getTime();
        var dateOut_arr = new Date(priceArray[i].date_to).getTime();
        var day = (priceArray[i].dayorsut == "дней") ? 1 : 0;

        var cur_price = this.actualCurPrice(priceArray[i]);

        //смотрим есть ли цены на дату заезда
        if (dateIn >= dateIn_arr && dateIn <= dateOut_arr) {
          isset_price.in = true
          type_tr.price = cur_price
          type_tr.dayorsut = (priceArray[i].dayorsut == "дней") ? 1 : 0;
        }
        //и дату выезда
        if (dateOut >= dateIn_arr && dateOut <= dateOut_arr) {
          isset_price.out = true
          type_tr.type = priceArray[i].type_transition
        }

        //узнаём максимальную и минимальную дату заданных цен
        if(isset_price.mindatein == 0) {
          isset_price.mindatein = dateIn_arr
        }
        if(dateIn_arr < isset_price.mindatein) {
          isset_price.mindatein = dateIn_arr
        }
        if(dateOut_arr > isset_price.maxdateout) {
          isset_price.maxdateout = dateOut_arr
        }

        if(dateIn >= dateIn_arr && dateOut <= dateOut_arr) { //если в одном массиве и начало и конец
          price =  ((dateOut - dateIn)/1000/60/60/24 + day)*cur_price
          info = priceArray[i].info
        } else if(dateIn >= dateIn_arr && dateIn < dateOut_arr) { //если в массиве только начало
          price += ((dateOut_arr - dateIn)/1000/60/60/24 + day)*cur_price
        } else if(dateOut > dateIn_arr && dateOut <= dateOut_arr) { //если в массиве только конец
          price += ((dateOut - dateIn_arr)/1000/60/60/24 + day)*cur_price
        }
		if(dateOut == dateIn_arr || dateIn == dateOut_arr) {
			price += (day)*cur_price
		}
      }

		price += (priceArray.length -1)*this.actualCurPrice(priceArray[priceArray.length -1])

      if(type_tr.type == "По дате заезда") {
        price = ((dateOut - dateIn)/1000/60/60/24 + type_tr.dayorsut)*type_tr.price
      }

        if(priceArray == undefined || priceArray.length == 0) {
          this.$notify.error({title:"Нет цен!!!", message:"Нет цен на указанные даты",duration: 0})
          return [false,info]
        } else if(!isset_price.in) {
          var d = new Date(isset_price.mindatein)
          this.$notify.error({title:"Нет цен!!!", message:"Цены утверждены начиная с "+formatDate(d),duration: 0})
          return [false,info]
        } else if(!isset_price.out) {
          var d = new Date(isset_price.maxdateout)
          this.$notify.error({title:"Нет цен!!!", message:"Цены утверждены только до "+formatDate(d),duration: 0})
          return [false,info]
        } else {
          return [+price.toFixed(2),info]
        }

  },
},
computed: {
  unique_category:  {
    get: function() {
    var outObj = {};
    for (var i = 0; i < this.appart_price.length; i++) {
      for (var key in this.appart_price[i]) {
        if (this.appart_price[i].hasOwnProperty(key)) {
          if(outObj.hasOwnProperty(key)) {
            if(!outObj[key].includes(this.appart_price[i][key])) {
              outObj[key].push(this.appart_price[i][key])
            }
          } else {
            outObj[key] = [this.appart_price[i][key]];
          }
        }
      }
    }
    return outObj;
    }
  },
  filter_dops: {
    get: function() {
      var arr = [];
      let set_time_in = this.allsettings.dates_in_out[0];
      let set_time_out = this.allsettings.dates_in_out[1];
      for (var i = 0; i < this.dops.variants_usl.length; i++) {

        var data_time_in = new Date(this.dops.variants_usl[i].date_from)
        var data_time_out = new Date(this.dops.variants_usl[i].date_to)
        if(set_time_in < data_time_in && data_time_out < set_time_out) {
          arr.push(this.dops.variants_usl[i])
        }
      }
      return arr
      }
  }
},
watch: {
},
mounted() {
	this.loading = true;
	  axios
	  .get('https://wg.belkurort.by/api/v1/appartment/all/with_price.php')
	  .then(response => {
		this.sanatoriums = response.data;

		axios
	  .get('https://www.nbrb.by/API/ExRates/Rates?Periodicity=0')
	  .then(response => {
		this.curs_from_nb = response.data;
		this.loading = false;
	  })
	  .catch(function () {
      this.$message.error('Ошибка! Не удалось подгрузить курсы валют');
      window.location.reload();
	  });
	  }).catch(function () {
      this.$message.error('Ошибка! Не удалось загрузить список санаториев');
      window.location.reload();
	  });
    this.updatePrices();
    this.allsettings.dates_in_out = [new Date(),addDays(new Date(),7)];
}
});

function formatDate(date) {
  var dd = date.getDate();
  if (dd < 10) dd = '0' + dd;
  var mm = date.getMonth() + 1;
  if (mm < 10) mm = '0' + mm;
  var yy = date.getFullYear();
  if (yy < 10) yy = '0' + yy;
  return dd + '.' + mm + '.' + yy;
}

function addDays(date, days) {
  var result = new Date(date);
  result.setDate(result.getDate() + days);
  return result;
}
