<template>
<el-container>
  <el-aside width="300px">
    <el-menu default-active="1">
      <el-submenu index="0">
        <template slot="title">
          <i class="el-icon-user"></i>
          <span>Пользователь</span>
        </template>
        <el-menu-item index="exit" @click="logout()">
          Выйти
        </el-menu-item>

      </el-submenu>
      <el-menu-item>
        <el-select v-model="san" placeholder="Выберите санаторий" filterable>
          <el-option v-for="item in sanatoriums" :key="item.id" :label="item.name" :value="item.id">
            <span style="float: left">{{ item.name }} ({{ item.type }}) </span>
            <span style="float: right; color: #8492a6; font-size: 13px; padding-left: 10px;"> {{ item.oblast }}</span>
          </el-option>
        </el-select>
      </el-menu-item>

    </el-menu>
  </el-aside>
  <router-view :data="sanatoriums"></router-view>
</el-container>
</template>

<script>
import {
  AUTH_LOGOUT
} from '../store/actions/auth'

export default {
  name: 'adminCab',
  props: {

  },
  components: {

  },
  data() {
    return {
      sanatoriums: [],
      san: ""
    }
  },
  methods: {
    logout: function() {
      this.$store.dispatch(AUTH_LOGOUT).then(() => this.$router.push('/login'))
    }
  },
  mounted() {
    this.$api.get('appartment/all/index.php?key=' + localStorage.getItem("user-token"))
      .then(response => (this.sanatoriums = response.data));
  },
  watch: {
    san: function(a) {
      this.$router.push('/price-table/' + a)
    }
  }
}
</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
