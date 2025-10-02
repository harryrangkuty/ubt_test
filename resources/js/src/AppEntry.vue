<script>
export default {
  props: {
    menu: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      routeKey: "home",
      collapsed: false,
      drawerVisible: false,
      openKeys: [],
      selectedKeys: [],
      isMobile: window.innerWidth <= 768
    };
  },

  watch: {
    collapsed(value) {
      this.setLocalStorage('sidebar-collapsed', value)
    }
  },

  created() {
    this.openKeys = [this.route.substr(1)]
    this.selectedKeys = [this.fullRoute]

    if (this.isMobile) {
      this.collapsed = true // mobile default collapsed
    } else {
      this.collapsed = this.getLocalStorage('sidebar-collapsed', true);
    }
  },

  mounted() {
    window.addEventListener("resize", this.handleResize);
  },

  beforeUnmount() {
    window.removeEventListener("resize", this.handleResize);
  },

  methods: {
    handleResize() {
      this.isMobile = window.innerWidth <= 768;
      if (this.isMobile) {
        this.collapsed = true;
      }
    },
    accordion() {
      if (this.openKeys.length > 1)
        this.openKeys.shift()
    },
    toggleLogoClick() {
      if (this.isMobile) {
        this.drawerVisible = true; // buka drawer di mobile
      } else {
        this.collapsed = !this.collapsed; // toggle sider di desktop
      }
    },
    async switchRole(id) {
      const vm = this;
      vm.loadingTrue()

      if (vm.user.active_role_id === id) return;

      const params = {
        id: vm.user.id,
        req: 'switch_role',
        role_id: id
      };
      const response = await vm.axios.post(`/profile/write`, params).catch((e) => vm.$onAjaxError(e));
      if (response && response.data) {
        vm.openNotification('Berhasil mengubah Role, refreshing page...', 'success');
        setTimeout(() => {
          window.location.replace('/dashboard');
        }, 500);
        vm.showModal = false;
      }
    },
  }
};
</script>

<template>
  <a-layout class="min-h-screen h-full">
    <!-- Menu Desktop -->
    <a-layout-sider v-model:collapsed="collapsed" :trigger="null" theme="light" width="250" collapsed-width="65"
      class="!bg-purple-100 shadow-md overflow-auto h-screen !fixed left-0 top-0 bottom-0" collapsible>
      <a class="flex flex-col mt-4 mb-1 cursor-pointer" @click.prevent="toggleLogoClick">
        <div class="flex flex-col items-center">
          <div class="relative inline-block p-2">
            <template v-if="!collapsed">
              <span
                class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-purple-400 rounded-tl-md transition duration-300"></span>
              <span
                class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-purple-400 rounded-tr-md transition duration-300"></span>
              <span
                class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-purple-400 rounded-bl-md transition duration-300"></span>
              <span
                class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-purple-400 rounded-br-md transition duration-300"></span>
            </template>
            <img :src="publicPath('images/main-logo.webp')" :class="[
              'rounded-full shadow-xl transition-all duration-500 ease-in-out',
              collapsed
                ? 'ring-4 ring-purple-300 hover:ring-purple-500'
                : 'h-20 w-20 ring-4 ring-purple-400 hover:ring-purple-700'
            ]" />
          </div>
          <div v-if="!collapsed" class="mt-5 text-center space-y-1">
            <h1
              class="text-2xl font-extrabold bg-gradient-to-r from-purple-700 via-purple-500 to-pink-500 bg-clip-text text-transparent tracking-wide drop-shadow-sm">
              UBT Test
            </h1>
            <p class="text-xs font-medium uppercase tracking-wider">
              <span class="text-purple-400">Universitas </span>
              <span class="text-yellow-500 font-extrabold">Bunda Thamrin</span>
            </p>
          </div>
        </div>
        <div v-if="!collapsed"
          class="mt-4 h-px bg-gradient-to-r from-yellow-400/70 via-yellow-300/80 to-transparent">
        </div>
      </a>
      <AppMenu :menu="menu" v-model:selectedKeys="selectedKeys" v-model:openKeys="openKeys" :collapsed="collapsed"
        :accordion="accordion" />
      <div class="p-3 text-center text-xs font-bold text-purple-700 border-t border-purple-200">
        versi 1.0
      </div>
    </a-layout-sider>
    <!-- Menu Mobile -->
    <a-drawer v-model:open="drawerVisible" placement="left" :width="250" :closable="false" :body-style="{ padding: 0 }">
      <template #title>
        <div class="flex items-center justify-between">
          <span class="font-bold text-purple-950">📂 Menu Aplikasi</span>
          <button @click="drawerVisible = false" class="p-2 rounded-full hover:bg-purple-200 transition">
            <Icon icon="bi:x-lg" class="text-xl text-purple-900" />
          </button>
        </div>
      </template>
      <AppMenu :menu="menu" v-model:selectedKeys="selectedKeys" v-model:openKeys="openKeys" :collapsed="collapsed"
        :accordion="accordion" />
      <template #footer>
        <div class="text-center font-bold text-purple-700">versi 1.0</div>
      </template>
    </a-drawer>
    <a-layout :class="[collapsed ? 'ml-[65px]' : 'ml-[250px]', 'transition-all duration-400 ease-in-out']">
      <a-layout-header
        class="!bg-gradient-to-r !from-purple-500 !to-purple-950 !px-2 md:!px-10 !h-[52px] !text-white sticky top-0 z-50 flex gap-x-2 justify-between items-center shadow-lg ml-1.5 rounded-bl-md mb-4 relative">
        <div class="flex items-center justify-between w-full">
          <div class="flex md:gap-x-6 items-center">
            <Icon :icon="collapsed ? 'line-md:menu-fold-right' : 'line-md:menu-fold-left'"
              @click.prevent="toggleLogoClick" class="!z-10 text-2xl cursor-pointer" />
          </div>
          <div class="flex items-center min-w-0">
            <a-dropdown>
              <a class="ant-dropdown-link">
                <div v-if="!isMobile" class=" flex items-center space-x-2">
                  <img :src="user.photo ? user.photo : '/images/user-icon.webp'"
                    class="h-10 w-10 rounded-full mr-2.5 object-cover object-center ring-2 ring-white/30 hover:ring-white/60 transition" />
                  <div class="flex flex-wrap items-center gap-x-1">
                    <span
                      class="md:text-lg text-base text-white font-semibold whitespace-nowrap overflow-hidden text-ellipsis">
                      {{ user.name }}
                    </span>
                    <span class="text-xs text-white bg-white/20 px-2 py-0.5 rounded-full font-medium">
                      {{ user.unit?.code || user.unit?.name || '-' }}
                    </span>
                    <span v-if="user.active_role"
                      class="text-xs bg-blue-600 text-white px-2 py-0.5 rounded-full font-medium">
                      {{ user.active_role?.display_name }}
                    </span>
                  </div>
                </div>
                <div v-else>
                  <div
                    class="flex items-center gap-x-2 max-w-[280px] px-2 py-1 rounded-lg  hover:bg-white/20 transition-all duration-300">
                    <span class="text-sm text-white truncate font-medium tracking-wide">
                      {{ user.name }}
                    </span>
                    <img :src="user.photo ? user.photo : '/images/user-icon.webp'"
                      class="h-9 w-9 rounded-full object-cover object-center ring-2 ring-white/30 hover:ring-white/60 transition" />
                  </div>
                </div>
              </a>
              <template #overlay>
                <a-menu class="w-56">
                  <div class="px-3 py-4 text-center border-b border-gray-200">
                    <img :src="user.photo ? user.photo : '/images/user-icon.webp'"
                      class="h-16 w-16 mx-auto rounded-full object-cover object-center ring-2 ring-purple-300" />
                    <div class="mt-2">
                      <div class="text-sm font-semibold text-gray-800 truncate">
                        {{ user.name }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ user.unit?.code || user.unit?.name || '-' }}
                      </div>
                      <div v-if="user.active_role"
                        class="mt-1 inline-block text-xs bg-blue-600 text-white px-2 py-0.5 rounded-full font-medium">
                        {{ user.active_role?.display_name }}
                      </div>
                    </div>
                  </div>
                  <a-menu-item key="profile">
                    <a href="/profile" class="flex items-center font-medium">
                      <Icon icon="line-md:account" class="mr-2 text-blue-500" />
                      Profil
                    </a>
                  </a-menu-item>
                  <a-divider v-if="user.roles.length > 1" class="my-2" />
                  <a-sub-menu v-if="user.roles.length > 1" key="switch-role" class="flex items-center asas">
                    <template #title class="flex items-center asas">
                      <span class="flex items-center">
                        <Icon icon="line-md:watch" class="mr-2 text-blue-500" />
                        Ganti Hak Akses
                      </span>
                    </template>
                    <a-menu-item v-for="(role, k) in user.roles" :key="role.id" @click="switchRole(role.id)" :style="user.active_role_id === role.id ? {
                      backgroundColor: '#bfdbfe',   // Tailwind bg-blue-200
                      color: '#2563eb',             // Tailwind text-blue-600
                      fontWeight: '600'             // Tailwind font-semibold
                    } : {}">
                      {{ k + 1 }}. {{ role.display_name }}
                    </a-menu-item>
                  </a-sub-menu>
                  <a-divider v-if="user.roles.length > 1" class="my-2" />
                  <a-menu-item key="logout">
                    <a href="/logout" class="flex items-center font-medium text-red-500 hover:text-red-600">
                      <Icon icon="ant-design:logout-outlined" class="mr-2 text-red-500" />
                      Logout
                    </a>
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </div>
        </div>
      </a-layout-header>
      <a-layout-content class="px-2">
        <slot></slot>
      </a-layout-content>
      <a-layout-footer class="!p-0 !bg-transparent">
        <div class="w-full text-center text-gray-700 text-sm md:text-base"
          style="background-image: linear-gradient(90deg, rgba(149, 131, 198, 0) 0%, rgba(149, 131, 198, 0.6) 33%, rgba(149, 131, 198, 0.3) 66%, rgba(149, 131, 198, 0) 100%);">
          <div class="px-3 py-2 md:py-3 flex flex-col md:flex-row justify-center items-center gap-y-1 md:gap-x-2">
            <span><b>UBT Test</b> ©2025 Designed and Programmed by</span>
            <span class="font-semibold">Harry Rangkuti, A.Md.Kom</span>
            <span class="hidden md:inline">|</span>
            <span class="font-semibold">PT. Thamrin Sinar Surya</span>
          </div>
        </div>
      </a-layout-footer>

    </a-layout>
  </a-layout>
</template>

<style scoped>
@keyframes zoomIn {
  0% {
    transform: scale(0.5);
    opacity: 0;
  }

  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.icon-animate {
  animation: zoomIn 1s ease-out forwards;
}

:deep(.ant-dropdown-menu-submenu-title) {
  display: flex !important;
  align-items: center !important;
}
</style>