import { defineStore } from "pinia";
import { usePage } from "@inertiajs/vue3";
const useAppStore = defineStore("app", {
    state: () => {
        return {
            user: {},
            mainUrl: "",
            isNative: false,
            platform: "web",
            appInfo: {},
            isMobile: false,
            mobileActiveMenu: "home",
            env: "",
        };
    },
    actions: {
        setMobile(isMobile) {
            this.isMobile = isMobile;
        },
        setUser(user) {
            this.user = user;
        },
        isLoggedIn() {
            return this.user.hasOwnProperty("id");
        },
        setMainUrl(url) {
            this.mainUrl = url;
        },
        setPlatform(platform) {
            this.platform = platform;
        },
        setNative(native) {
            this.isNative = native;
            this.isMobile = true;
        },
        setAppInfo(appInfo) {
            this.appInfo = appInfo;
        },
        setMobileMenu(menu) {
            this.mobileActiveMenu = menu;
        },
        setEnv(env) {
            this.env = env;
        },
    },
});

export default useAppStore;
