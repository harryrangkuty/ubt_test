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
      openKeys: [],
      selectedKeys: [],
    };
  },

  watch: {
    'collapsed': function (value) {
      this.setLocalStorage('sidebar-collapsed', value)
    }
  },

  created() {
    this.openKeys = [this.route.substr(1)]
    this.selectedKeys = [this.fullRoute]
    if (window.innerWidth <= 768) {
      this.collapsed = true;
    } else {
      this.collapsed = this.getLocalStorage('sidebar-collapsed', true);
    }
  },

  methods: {
    accordion() {
      if (this.openKeys.length > 1)
        this.openKeys.shift()
    },
  }
};
</script>

<template>
  <a-layout class="min-h-screen h-full">
    <KeepAlive>
      <a-layout-sider v-model:collapsed="collapsed" :trigger="null" theme="light" width="250" collapsed-width="65"
        class="!bg-purple-100 shadow-md overflow-auto h-screen !fixed left-0 top-0 bottom-0" overflow-y collapsible>
        <a class="flex justify-center m-4" @click.prevent="() => (collapsed = !collapsed)">
          <div>
            <img v-if="collapsed" :src="publicPath('images/main-logo.webp')" />
            <img v-else :src="publicPath('images/main-logo.webp')" class="h-44 w-44" />
          </div>
        </a>
        <a-menu v-model:selectedKeys="selectedKeys" v-model:openKeys="openKeys" mode="inline" @openChange="accordion"
          class="bg-purple-100">
          <a-menu-item key="dashboard">
            <template #icon>
              <Icon icon="bi:grid" class="icon-animate !text-2xl !text-purple-950" />
            </template>
            <a :href="publicPath('dashboard')">
              <span class="title-group">Dashboard</span>
            </a>
          </a-menu-item>
          <template v-for="m in menu">
            <template v-if="!m.key">
              <hr>
              <a-menu-item v-if="!collapsed" :key="m.title">
                <span class="title-group">{{ m.title }}</span>
              </a-menu-item>
            </template>
            <template v-else>
              <template v-if="m.submenu">
                <a-sub-menu :key="m.key" :title="m.title">
                  <template v-if="m.icon" #icon>
                    <Icon :icon="m.icon" class="icon-animate !text-2xl !text-purple-950" />
                  </template>
                  <a-menu-item v-for="sm in m.submenu" :key="sm.key">
                    <template v-if="sm.icon" #icon>
                      <Icon :icon="sm.icon" class="!text-base" />
                    </template>
                    <a :href="publicPath(sm.url)">{{ sm.title }}</a>
                  </a-menu-item>
                </a-sub-menu>
              </template>
              <template v-else>
                <a-menu-item :key="m.key">
                  <template v-if="m.icon" #icon>
                    <Icon :icon="m.icon" class="icon-animate !text-2xl !text-purple-950" />
                  </template>
                  <a :href="publicPath(m.url)">{{ m.title }}</a>
                </a-menu-item>
              </template>
            </template>
          </template>
        </a-menu>
      </a-layout-sider>
    </KeepAlive>
    <a-layout :class="[collapsed ? 'ml-[65px]' : 'ml-[250px]', 'transition-all duration-400 ease-in-out']">
      <a-layout-header
        class="!bg-gradient-to-r !from-purple-500 !to-purple-950 !px-2 md:!px-10 !h-[52px] !text-white sticky top-0 z-50 flex gap-x-2 justify-between items-center shadow-lg ml-1.5 rounded-bl-md mb-4 relative">
        <div class="flex items-center justify-between w-full">
          <div class="flex md:gap-x-6 items-center">
            <Icon :icon="collapsed ? 'line-md:menu-fold-right' : 'line-md:menu-fold-left'"
              @click.prevent="() => (collapsed = !collapsed)" class="!hidden md:!block !z-10 text-2xl cursor-pointer" />
          </div>
          <div class="flex items-center min-w-0">
            <a-dropdown>
              <a class="ant-dropdown-link flex items-center space-x-2">
                <img :src="user.photo ? '/storage/' + user.photo : '/images/user-icon.webp'"
                  class="h-10 w-10 rounded-full mr-2.5 object-cover object-center" />
                <span class="text-lg text-white font-semibold whitespace-nowrap overflow-hidden text-ellipsis">
                  {{ user.name }}
                </span>
                <span class="text-xs text-white">
                  ({{ user.unit?.code || user.unit?.name || '-' }})
                </span>
                <span class="text-xs text-white md:block hidden">
                  ({{ user.roles[0].display_name }})
                </span>
              </a>
              <template #overlay>
                <a-menu class="w-52">
                  <a-menu-item key="profile">
                    <a href="/profile" class="font-semibold flex">
                      <Icon icon='line-md:account' class="mr-2 text-blue-500" />Profil
                    </a>
                  </a-menu-item>
                  <a-menu-item key="logout">
                    <a href="/logout" class="font-semibold flex">
                      <Icon icon='ant-design:logout-outlined' class="mr-2 text-red-500" />Logout
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
      <a-layout-footer>
        <div class="w-full"
          style="background-image: linear-gradient(90deg, rgba(149, 131, 198, 0) 0%, rgba(149, 131, 198, 0.6) 33%, rgba(149, 131, 198, 0.3) 66%, rgba(149, 131, 198, 0) 100%);">
          <div class="text-center py-2.5">
            <span class="font-semibold">UBT TEST</span> ©2025 Created by <span class="font-semibold">IT PT. Thamrin Surya
              Group</span>
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
</style>