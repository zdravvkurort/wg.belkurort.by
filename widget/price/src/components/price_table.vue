<template>
<el-container>
  <el-main>
    <h3>{{getAppartName($route.params.id)}}</h3>
    <el-tabs v-model="activeTab">
      <el-tab-pane label="Цены" name="prices">
        <el-row type=" flex" justify="center" :gutter="10">
          <el-col>
            <template>
              <el-row type="flex">
                <el-col>
                  <add-price v-on:successAddPrice="getPriceTable()">
                  </add-price>
                </el-col>
              </el-row>
              <el-table :data="newTable" style="width: 100%;margin-bottom: 20px;" row-key="tableId" v-loading="loading">
                <el-table-column prop="group" label="Даты" width="250">
                </el-table-column>
                <el-table-column label="Категория номера" prop="type_appart" sortable width="180">
                </el-table-column>
                <el-table-column label="Тип места" prop="type_book" sortable width="130">
                </el-table-column>
                <el-table-column label="Национальность" prop="nationality" sortable width="120">
                </el-table-column>
                <el-table-column label="Возраст" sortable width="100">
                  <template slot-scope="props">
                    {{props.row.old_from}}-{{props.row.old_to}}
                  </template>
                </el-table-column>
                <el-table-column label="Питание" prop="pitanie" sortable width="120">
                </el-table-column>
                <el-table-column label="Лечение" prop="health" sortable width="120">
                </el-table-column>
                <el-table-column label="Тип перехода" prop="type_transition" sortable width="140">
                </el-table-column>
                <el-table-column label="Цена в рос руб" prop="price_rub" sortable width="100">
                </el-table-column>
                <el-table-column label="Цена в бел руб" prop="price_byn" sortable width="100">
                </el-table-column>
                <el-table-column label="Цена в долларах" prop="price_usd" sortable width="100">
                </el-table-column>
                <el-table-column label="Цена в евро" prop="price_euro" sortable width="100">
                </el-table-column>
                <el-table-column label="Инфо" prop="info" sortable width="180">
                </el-table-column>
                <el-table-column label="Действия" fixed="right" align="right" width="100">
                  <template slot-scope="scope">
                    <el-button type="primary" icon="el-icon-edit" circle size="mini" @click="handleEdit(scope.$index, scope.row)"></el-button>
                    <el-button type="danger" icon="el-icon-delete" circle size="mini" @click="handleDelete(scope.$index, scope.row)"></el-button>
                  </template>
                </el-table-column>

              </el-table>
            </template>
          </el-col>
        </el-row>
        <edit-price :showEditprice="showEditprice" :priceInfo="priceInfo" v-on:closeeditmodal="showEditprice = false; getPriceTable()" @foundDoublePrice='setTimeout(checkActiveRow(+$event),1000)'>
        </edit-price>
      </el-tab-pane>
      <el-tab-pane label="Категории номеров" name="category">
        <el-button type="success">Добавить категорию номера</el-button>
        <el-table :data="categoryTable" style="width: 100%;margin-bottom: 20px;" row-key="tableId" v-loading="loading">
          <el-table-column label="id" prop="id" width="50">
          </el-table-column>
          <el-table-column label="Название категории" prop="name">
          </el-table-column>
          <el-table-column label="Количество основных мест" prop="main_place" width="250">
          </el-table-column>
          <el-table-column label="Количество дополнительных мест" prop="main_place" width="260">
          </el-table-column>
          <el-table-column label="Действия" fixed="right" align="right" width="100">
            <template>
              <el-button type="primary" icon="el-icon-edit" circle size="mini" @click='alert()'></el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>
      <el-tab-pane label="Настройки санатория" name="settings">

        <el-form ref="form" :model="form" label-width="220px">
          <el-form-item label="Область">
            <el-select v-model="form.region" placeholder="Выберите область санатория">
              <el-option label="Минская область" value="Минская область"></el-option>
              <el-option label="Брестская область" value="Брестская область"></el-option>
              <el-option label="Витебская область" value="Витебская область"></el-option>
              <el-option label="Гомельская область" value="Гомельская область"></el-option>
              <el-option label="Гродненская область" value="Гродненская область"></el-option>
              <el-option label="Могилевская область" value="Могилевская область"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="Адрес">
            <el-input type="textarea" v-model="form.address"></el-input>
          </el-form-item>
          <el-form-item label="По дням или суткам?">
            <el-select v-model="form.dayorsut" placeholder="Выберите категорию номера">
              <el-option label="Сутки" value="суток"></el-option>
              <el-option label="Дни" value="дней"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="Время заезда">
            <el-time-select placeholder="Время заезда" v-model="form.startTime" :picker-options="{ start: '00:00',
                                                                                            step: '01:00',
                                                                                            end: '24:00'
                                                                                          }">
            </el-time-select>
          </el-form-item>
          <el-form-item label="Время выезда">
            <el-time-select placeholder="Время выезда" v-model="form.endTime" :picker-options="{     start: '00:00',
                                                                                            step: '01:00',
                                                                                            end: '24:00'
                                                                                          }">
            </el-time-select>
          </el-form-item>
          <el-form-item label="Email для отправки путёвок">
            <el-input v-model="form.email"></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary">Сохранить</el-button>
          </el-form-item>
        </el-form>

      </el-tab-pane>
    </el-tabs>
  </el-main>
  <el-footer></el-footer>
</el-container>
</template>

<script>
import addPrice from './add_price.vue'
import editPrice from './edit_price.vue'

export default {
  name: 'priceTable',
  props: ['data'],
  components: {
    addPrice,
    editPrice
  },
  data() {
    return {
      tableData: [],
      priceInfo: null,
      currentRow: null,
      showEditprice: false,
      loading: true,
      activeTab: "prices",
      categoryTable: [],
      form: {
        id: '',
        address: '',
        dayorsut: '',
        region: '',
        startTime: '',
        endTime: '',
        email: "",
      },
    }
  },
  methods: {
    handleEdit(index, row) {
      var trig = false;
      for (var i = 0; i < this.tableData.length; i++) {
        if (this.tableData[i].id == row.id) {
          this.priceInfo = this.tableData[i]
          trig = true
        }
      }
      if (trig == false) {
        this.priceInfo = {}
      }
      this.showEditprice = true
    },
    checkActiveRow(row) {
      for (var i = 0; i < this.tableData.length; i++) {
        if (this.tableData[i].id == row) {
          this.$refs.singleTable.setCurrentRow(this.tableData[i])
        }
      }
    },
    handleCurrentChange(val) {
      this.currentRow = val;
    },
    getAppartName(id) {
      for (var i = 0; i < this.data.length; i++) {
        if (this.data[i].id == id) {
          return this.data[i].type + " " + this.data[i].name
        }
      }
    },
    getPriceTable() {
      this.loading = true
      this.$api
        .get('prices/index.php?key=' + localStorage.getItem("user-token") + '&appart_id=' + this.$route.params.id)
        .then(response => {
          if (response.data == "auth error") {
            this.tableData = []
            this.$message.error('Выйдите и войдите в систему')
            this.$router.push('/login')
          } else {
            this.tableData = response.data
          }
          this.loading = false
        });
    },
    handleDelete(index, row) {
      this.$api
        .get('prices/delete.php?key=' + localStorage.getItem("user-token") + '&price_id=' + row.id)
        .then(response => {
          if (!response.data.error) {
            this.$message({
              message: 'Удалили цену',
              type: 'success'
            });
            this.getPriceTable();
          } else if (response.data == "auth error") {
            this.tableData = []
            this.$message.error('Выйдите и войдите в систему')
            this.$router.push('/login')
          } else {
            this.$message.error({
              message: response.data.message
            })
          }
        })
        .catch(() => {
          this.$message.error({
            message: "Произошла ошибка. Попробуйте удалить цену ещё раз!"
          })
        });
    },
    closeModaleditprice(done) {
      done();
    },
  },
  mounted() {
    this.getPriceTable()
  },
  beforeRouteUpdate(to, from, next) {
    next()
    this.getPriceTable()
    this.$api
      .get('appartment/info/index.php?key=' + localStorage.getItem("user-token") + '&appart_id=' + this.$route.params.id)
      .then(response => (this.categoryTable = response.data));
    this.$api
      .get('appartment/info/settings.php?key=' + localStorage.getItem("user-token") + '&appart_id=' + this.$route.params.id)
      .then(response => (this.form = response.data));
  },
  computed: {
    newTable: function() {
      return toGroup(this.tableData)
    }
  }

}

function toGroup(myArray) {
  var groups = {};
  for (var i = 0; i < myArray.length; i++) {
    var groupName = 'с ' + myArray[i].date_from + " по " + myArray[i].date_to;
    if (!groups[groupName]) {
      groups[groupName] = [];
    }
    myArray[i].group = groupName
    groups[groupName].push(myArray[i]);
  }
  myArray = [];
  for (groupName in groups) {
    myArray.push({
      tableId: (myArray.length + 1),
      group: groupName,
      children: groups[groupName]
    });
  }
  for (var x = 0; x < myArray.length; x++) {
    for (var j = 0; j < myArray[x].children.length; j++) {
      myArray[x].children[j].tableId = String(myArray[x].tableId) + String(j + 1)
    }
  }
  return myArray
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
