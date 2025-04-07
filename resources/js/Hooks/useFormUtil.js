import { watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import each from "lodash/each";
import has from "lodash/has";

export default function useFormUtil() {
    const watchErrors = (form, formRef) => {
        watch(
            () => form.errors || usePage().props.errors,
            (errors) => {
                if (!errors) return;
                
                const errorFields = {};
                each(errors, (message, field) => {
                    errorFields[field] = {
                        status: "error",
                        message: Array.isArray(message) ? message[0] : message,
                    };
                });
                formRef.value?.setFields(errorFields);
            },
            { deep: true }
        );
    };

    return { watchErrors };
}
