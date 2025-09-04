<script>

const columns = [
    {
        title: "Name",
        dataIndex: 'name',
    },
    {
        title: "Code",
        dataIndex: 'code',
    },
    {
        title: "Type",
        dataIndex: 'type',
    },
    {
        title: "Level",
        dataIndex: 'level',
    },
    {
        title: "NIM Label",
        dataIndex: 'nim_label',
    },
    {
        title: "Action",
        key: "action",
        align: "left",
        width: 100,
        fixed: 'right',
    },
];

export default {
    props: {
        constant: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            columns,
            filter: {
                unit_type: null,
            },
            form: {
                id: null,
                name: null,
                code: null,
                type: null,
                level: null,
                nim_label: null,
                is_active: false,
                jenis: null,
                is_satker: false,
                is_fakultas: false,
                is_prodi: false,
            },
        };
    },

    mounted() {
        this.readData()
    },

    methods: {
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
                ...vm.filter
            };

            const response = await vm.axios.get(vm.readRoute, { params })
            if (response && response.data) {
                const pagination = { ...vm._pagination };
                pagination.total = response.data.models.total;
                vm.loadingFalse();
                vm.models = response.data.models.data;
                vm._pagination = pagination;
            }
        },

        newData() {
            const vm = this;
            Object.assign(vm.$data.form, vm.$options.data().form);
            vm.$nextTick(function () {
                vm.showModal = true;
            })
        },

        editData(m) {
            const vm = this;
            vm.form = vm.lodash.cloneDeep(m)
            vm.$nextTick(function () {
                vm.showModal = true;
                if (m.is_satker) {
                    vm.form.jenis = 'satker';
                } else if (m.is_fakultas) {
                    vm.form.jenis = 'fakultas';
                } else if (m.is_prodi) {
                    vm.form.jenis = 'prodi';
                }
            })
        },

        async writeData() {
            const vm = this;
            vm.loadingTrue()
            const form = { 
                req: 'write', 
                ...vm.form 
            };
            const response = await vm.axios.post(vm.writeRoute, form).catch((e) => vm.$onAjaxError(e));
            if (response && response.data) {
                vm.openNotification('Berhasil menyimpan data', 'success');
                vm.readData();
                vm.showModal = false;
            }
        },

        async deleteData(id) {
            const vm = this;
            vm.loadingTrue();
            const form = {
                req: 'delete',
                id: id
            };
            const response = await vm.axios.post(vm.writeRoute, form).catch((e) => vm.$onAjaxError(e));
            if (response && response.data) {
                vm.openNotification('Berhasil menghapus data', 'success');
                vm.readData();
                vm.showModal = false;
            }
        },

        onJenisChange() {
            this.form.is_satker = false;
            this.form.is_fakultas = false;
            this.form.is_prodi = false;

            if (this.form.jenis === 'satker') {
                this.form.is_satker = true;
            } else if (this.form.jenis === 'fakultas') {
                this.form.is_fakultas = true;
            } else if (this.form.jenis === 'prodi') {
                this.form.is_prodi = true;
            }
        },
    }
};
</script>

<template>
    <a-row type="flex" justify="center">
        <a-col :span="24">
            <a-card class="card">
                <a-row class="flex flex-wrap items-center justify-between mb-4 pb-4 border-b-2">
                    <h1 class="text-base font-semibold mb-4 md:mb-0">Manajemen Unit</h1>
                      <div class="flex justify-end items-end w-full md:w-auto">
                        <a-row class="flex flex-wrap justify-start sm:justify-end gap-2 items-center">
                            <a-col class="sm:w-auto w-full">
                                <a-select v-model:value="filter.unit_type" show-search allow-clear 
                                    option-filter-prop="title"placeholder="Tipe Unit" class=" min-w-[10rem] w-full"
                                    @change="readData">
                                    <a-select-option v-for="i in constant.TYPES" :key="i.type" :title="i.type"
                                        :value="i.type">
                                        {{ i.type }}
                                    </a-select-option>
                                </a-select>
                            </a-col>
                            <a-col class="sm:w-auto w-full">
                                <a-input v-model:value="filter.search" class="min-w-[10rem] lg:min-w-[20rem] w-full" @keyup.enter="readData" placeholder="Cari Unit ...">
                                    <template #addonAfter>
                                        <Icon icon='ant-design:search-outlined' />
                                    </template>
                                </a-input>
                            </a-col>
                            <a-col class="w-full sm:w-auto">
                                <a-button class="flex items-center justify-center w-full" type="primary" @click="newData()">Tambah Unit</a-button>
                            </a-col>
                        </a-row>
                    </div>
                </a-row>
                <a-table :scroll="{ x: 800 }" :columns="columns" :row-key="(obj) => obj.id" :pagination="_pagination"
                    :loading="loadingStatus" :data-source="models" @change="handleTableChange">
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'action'">
                            <a-button-group>
                                <a-button size="small" type="primary" @click="editData(record)">
                                <Icon icon='ant-design:form-outlined' />
                                </a-button>
                                <a-popconfirm title="Yakin menghapus data?" @confirm="deleteData(record.id)">
                                <a-button size="small" type="primary" danger>
                                    <Icon icon="ant-design:delete-outlined"/>
                                </a-button>
                                </a-popconfirm>
                            </a-button-group>
                        </template>
                    </template>
                </a-table>
            </a-card>
        </a-col>
    </a-row>
    <a-modal v-model:open="showModal" :title="form.id ? 'Ubah Data' : 'Tambah Data'" width="700px" @ok="writeData"
        :mask-closable="false" :destroy-on-close="true">
        <a-form ref="form" name="basic" :label-col="{ span: 8 }" :wrapper-col="{ span: 16 }">
            <a-form-item label="Name" data-column="name">
                <a-input v-model:value="form.name" placeholder="Nama Unit" />
            </a-form-item>
            <a-form-item label="Code" data-column="code">
                <a-input v-model:value="form.code" placeholder="Code Unit" />
            </a-form-item>
            <a-form-item label="Type" data-column="type">
                <a-select v-model:value="form.type" placeholder="Pilih tipe Unit">
                    <a-select-option v-for="i in constant.TYPES" :key="i.type" :title="i.type" :value="i.type">
                        {{ i.type }}
                    </a-select-option>
                </a-select>
            </a-form-item>
            <a-form-item label="Level" data-column="level">
                <a-input v-model:value="form.level" placeholder="Level Unit" />
            </a-form-item>
            <a-form-item label="NIM Label" data-column="nim_label">
                <a-input v-model:value="form.nim_label" placeholder="NIM Label" />
            </a-form-item>
            <a-form-item label="Status Aktif" data-column="is_active">
                <a-checkbox :checked="form.is_active" @change="form.is_active = !form.is_active">Aktif</a-checkbox>
            </a-form-item>
            <a-form-item label="Jenis" data-column="jenis">
                <a-radio-group v-model:value="form.jenis" @change="onJenisChange">
                    <a-radio value="satker">Satuan Kerja</a-radio>
                    <a-radio value="fakultas">Fakultas</a-radio>
                    <a-radio value="prodi">Program Studi</a-radio>
                </a-radio-group>
            </a-form-item>
        </a-form>
    </a-modal>
</template>