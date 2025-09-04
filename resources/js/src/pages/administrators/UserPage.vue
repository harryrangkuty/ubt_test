<script>
import { debounce } from "lodash";

let lastSearchId = 0;

const columns = [

  {
    title: "Nama",
    dataIndex: "name",
  },
  {
    title: "Unit",
    dataIndex: ["unit", "name"],
  },
  {
    title: "Email",
    dataIndex: "email",
  },
  {
    title: "Hak Akses",
    key: "roles",
  },
  {
    title: "Action",
    key: "action",
    align: "left",
    width: 160,
    fixed: 'right',
  },
];
export default {
  props: {
    title: String,
    constant: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      columns,
      form: {
        sso_id: null,
        id: null,
        roles: [],
        unit_id: null,
      },
      filter: {
        status: "aktif",
        search: "",
        roles: [],
      },
      ssoUsers: [],
      selectedSsoUser: [],
      searchingSso: false,
      units: [],
    };
  },

  mounted() {
    this.readData();
    this.units = this.unitsMap();

  },

  methods: {

    unitsMap() {
      return this.constant.UNIT.map((x) => ({
        value: x.id,
        label: x.name,
      }));
    },

    async readData(v) {
      const vm = this;
      vm.loadingTrue();
      let params = v ?? {
        total: vm._pagination.total,
        page: vm._pagination.current,
        results: vm._pagination.pageSize,
      };

      params = {
        req: "table",
        ...params,
        ...vm.filter,
      };

      const response = await vm.axios.get(vm.readRoute, { params });
      if (response && response.data) {
        const pagination = { ...vm._pagination };
        pagination.total = response.data.models.total;
        vm.loadingFalse();
        vm.models = response.data.models.data;
        vm._pagination = pagination;
      }
    },

    editData(m) {
      const vm = this;
      vm.form = vm.lodash.cloneDeep(m);
      if (Array.isArray(vm.form.roles)) {
        vm.form.roles = vm.form.roles.map(role => role.id);
      } else {
        vm.form.roles = [];
      }
      vm.$nextTick(function () {
        vm.showModal = true;
      })
    },

    async writeData() {
      const vm = this;
      vm.loadingTrue();
      const form = {
        req: 'write',
        ...vm.form
      };
      const response = await vm.axios.post(vm.writeRoute, form).catch((e) => vm.$onAjaxError(e));
      if (response && response.data) {
        vm.showModal = false;
        vm.readData();
        vm.loadingFalse();
        vm.openNotification(vm.form.id ? 'Berhasil mengubah data' : 'Berhasil menyimpan data', 'success');
      }
    },

    async deleteData(id, req) {
      const vm = this;
      const param = { req, id };
      const response = await vm.axios
        .post(vm.writeRoute, param)
        .catch((error) => vm.$onAjaxError(error));

      if (response && response.data) {
        vm.readData();
        if (req == "restore") {
          vm.openNotification("Data berhasil dikembalikan", "success");
        } else {
          vm.openNotification("Data berhasil dihapus", "success");
        }
      }
    },

    async syncData() {
      const vm = this;
      vm.loadingTrue();

      try {
        const response = await vm.axios.get(vm.readRoute, {
          params: { req: "sync"}
        });

        if (response && response.data) {
          vm.openNotification("Sinkronisasi berhasil!", "success");
          vm.readData();
        }
      } catch (e) {
        vm.$onAjaxError(e);
      } finally {
        vm.loadingFalse();
      }
    },

    newData() {
      const vm = this;
      vm.form = vm.$options.data().form;
      vm.$nextTick(function () {
        vm.showModal = true;
      })
    },

    searchSsoUser: debounce(function (search) {
      const vm = this;
      lastSearchId++;
      const lastId = lastSearchId;
      vm.searchingSso = true;
      vm.axios
        .get(vm.readRoute, {
          params: {
            req: "api",
            name: encodeURIComponent(search.trim()),
          },
        })
        .then((res) => {
          if (lastId !== lastSearchId) {
            return;
          }
          vm.searchingSso = false;

          vm.ssoUsers = res.data;

          vm.ssoUsersData = Object.values(res.data).flat();

          vm.selectedSsoUser = vm.ssoUsersData.map((x) => ({
            id: x.id,
            name: x.name,
            email: x.email,
            unit_id: x.unit_id,
            unit_name: x.unit_name,
            photo: x.photo,
          }));
        });
    }, 200),

    selectSsoUser(value) {
      const vm = this;
      const user = vm.selectedSsoUser.find((x) => x.id === value);
      vm.form = {
        sso_id: user.id,
        name: user.name,
        photo: user.photo,
        email: user.email,
        unit_id: user.unit_id,
      };
      vm.units = [...this.unitsMap(), { label: user.unit_name, value: user.unit_id }];
    },

    deselectSsoUser() {
      const vm = this;
      vm.form = vm.$options.data().form;
    },
  },
};
</script>

<template>
  <a-row type="flex" justify="center">
    <a-col :span="24">
      <a-card class="card">
        <a-row class="flex flex-wrap items-center justify-between mb-4 pb-4 border-b-2">
          <h1 class="text-base font-semibold">Manajemen User</h1>
          <div class="flex justify-end items-end w-full md:w-auto">
            <a-row class="flex flex-wrap justify-start sm:justify-end gap-2 items-center">
              <a-col class="sm:w-auto w-full">
                <a-select v-model:value="filter.status" class="lg:w-40 w-full" @change="readData">
                  <a-select-option value="aktif">Aktif</a-select-option>
                  <a-select-option value="non_aktif">Non Aktif</a-select-option>
                </a-select>
              </a-col>
              <a-col class="sm:w-auto w-full">
                <a-select v-model:value="filter.roles" class="lg:w-64 w-full" mode="multiple" show-search
                  option-filter-prop="title" allow-clear @change="readData" placeholder="Cari role ...">
                  <a-select-option v-for="obj in constant.ROLE" :key="obj.id" :value="obj.id" :title="obj.name">
                    {{ obj.display_name }}
                  </a-select-option>
                </a-select>
              </a-col>
              <a-col class="sm:w-auto w-full">
                <a-input v-model:value="filter.search" @keyup.enter="readData" class="lg:w-64 w-full"
                  placeholder="Cari pengguna ...">
                  <template #addonAfter>
                    <Icon icon='ant-design:search-outlined' />
                  </template>
                </a-input>
              </a-col>
              <a-col class="w-full sm:w-auto">
                <a-button class="flex items-center justify-center w-full" @click="syncData" type="primary">
                  Sinkronisasi ke Pusat Data
                </a-button>
              </a-col>
              <a-col class="w-full sm:w-auto">
                <a-button class="flex items-center justify-center w-full" @click="newData" type="primary"> Tambah
                </a-button>
              </a-col>
            </a-row>
          </div>
        </a-row>
        <a-table :scroll="{ x: 800 }" :columns="columns" :row-key="(obj) => obj.id" :pagination="_pagination"
          :loading="loadingStatus" :data-source="models" @change="handleTableChange">
          <template #bodyCell="{ column, record }">
            <template v-if="column.key == 'action' && filter.status == 'aktif'">
              <a-button-group class="flex justify-center">
                <a-button size="small" type="text" @click="editData(record)" :style="{ padding: '0 5px' }">
                  <Icon icon="line-md:pencil-twotone" class="flex justify-center text-green-500 text-[24px]" />
                </a-button>
                <a-button size="small" type="text" @click="openDetail(record.id)" :style="{ padding: '0 5px' }">
                  <Icon icon="line-md:file-search-twotone" class="flex justify-center text-blue-500 text-[24px]" />
                </a-button>
                <a-popconfirm title="Yakin menghapus data?" @confirm="deleteData(record.id, 'delete')">
                  <a-button type="text" size="small" :style="{ padding: '0 5px' }">
                    <Icon icon="line-md:trash" class="flex justify-center text-red-500 text-[24px]" />
                  </a-button>
                </a-popconfirm>
              </a-button-group>
            </template>
            <template v-if="column.key == 'action' && filter.status == 'non_aktif'">
              <a-popconfirm title="Yakin merestore data?" @confirm="deleteData(record.id, 'restore')">
                <a-button size="small" type="primary">
                  <Icon icon="ant-design:rollback-outlined" />
                </a-button>
              </a-popconfirm>
            </template>
            <template v-if="column.key == 'roles'">
              {{record.roles.map(role => role.display_name).join(', ')}}
            </template>
          </template>
        </a-table>
      </a-card>
    </a-col>
  </a-row>
  <a-modal v-model:open="showModal" :title="form.id ? 'Ubah Data' : 'Tambah Data'" @ok="writeData"
    :mask-closable="false" :destroy-on-close="true">
    <a-form ref="form" :model="form" name="basic" :label-col="{ span: 6 }" :wrapper-col="{ span: 18 }">
      <a-form-item v-if="!form.id" label="User" data-column="sso_id" :rules="[{ required: true }]">
        <a-select v-model:value="form.sso_id" :filter-option="false"
          :not-found-content="searchingSso ? undefined : null" show-search allow-clear @search="searchSsoUser"
          @deselect="deselectSsoUser" @select="selectSsoUser">
          <template v-for="(group, key) in ssoUsers">
            <a-select-opt-group :label="key">
              <a-select-option v-for="obj in group" :key="obj.id" :value="obj.id">
                {{ obj.name }} ({{ obj.identifier }})
              </a-select-option>
            </a-select-opt-group>
          </template>
        </a-select>
      </a-form-item>
      <a-form-item label="Roles" data-column="roles" :rules="[{ required: true }]">
        <a-select v-model:value="form.roles" mode="multiple" show-search option-filter-prop="title" allow-clear>
          <a-select-option v-for="obj in constant.ROLE" :key="obj.id" :value="obj.id" :title="obj.name">
            {{ obj.display_name }}
          </a-select-option>
        </a-select>
      </a-form-item>
      <a-form-item label="Name" data-column="name" :rules="[{ required: true }]">
        <a-input v-model:value="form.name" autocomplete="off" readonly />
      </a-form-item>
      <a-form-item label="Email" data-column="email">
        <a-input v-model:value="form.email" autocomplete="off" readonly />
      </a-form-item>
      <a-form-item label="Unit" data-column="unit_id" :rules="[{ required: true }]">
        <a-select v-model:value="form.unit_id" show-search :options="units" option-filter-prop="label"
          :disabled="!form.sso_id" />
      </a-form-item>
      <a-form-item label="Foto" data-column="photo">
        <img v-if="form.photo" :src="form.photo" style="width: 100px" alt="Tidak ditemukan" />
      </a-form-item>
    </a-form>
  </a-modal>
</template>