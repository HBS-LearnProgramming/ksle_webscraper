import './bootstrap';
import "../css/less/arco.less";
import "../css/app.css";


import VueSweetalert2 from "vue-sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";
import ArcoVue from "@arco-design/web-vue";
import store from "./Store";
import isNil from "lodash/isNil";
import ArcoVueIcon from "@arco-design/web-vue/es/icon";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        const pageKey = Object.keys(pages).find(path => path.endsWith(`${name}.vue`));

        const page = !isNil(pageKey) ? pages[pageKey] : pages["./Pages/Error404.vue"];

        return page;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        const components = import.meta.glob("./Components/**/*.vue", { eager: true });
        Object.entries(components).forEach(([path, module]) => {
            const componentName = path.split("/").pop().replace(".vue", "");
            app.component(componentName, module.default);
        });

        app.use(ArcoVue)
            .use(store)
            .use(ArcoVueIcon)
            .use(VueSweetalert2)
            .mount(el);
    },
});

