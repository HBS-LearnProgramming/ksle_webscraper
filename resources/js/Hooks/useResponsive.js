import { watch } from "vue";
import { useGrid } from "vue-screen";
import { useAppStore } from "@/Store";
import { usePage } from "@inertiajs/vue3";

export default function useResponsive() {
    const appStore = useAppStore();
    const page = usePage();
    const grid = useGrid();

    watch(
        () => page.props.auth?.user,
        (newUser) => {
            if (newUser) {
                appStore.setUser(newUser);
            }
        },
        { immediate: true }
    );
    watch(
        () => grid,
        () => {
            if (grid.lg) {
                appStore.setMobile(false);
            } else {
                appStore.setMobile(true);
            }
        },
        { immediate: true, deep: true }
    );
}
