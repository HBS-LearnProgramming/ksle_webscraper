import { computed } from "vue";
import { useI18n } from "vue-i18n";
import Cookies from "js-cookie";

export default function useLocale() {
    const i18 = useI18n();

    const localeOptions = [
        { label: "中文", value: "zh" },
        { label: "English", value: "en" },
    ];

    const currentLocale = computed(() => {
        return i18.locale.value;
    });
    const changeLocale = (locale) => {
        if (i18.locale.value === locale.value) {
            return;
        }
        i18.locale.value = locale.value;

        Cookies.set("locale", i18.locale.value);

        window.location.reload();
        // localStorage.setItem('app-locale', locale.value)
    };

    const currentLocaleLabel = computed(() => {
        return localeOptions.find((x) => x.value == i18.locale.value)?.label;
    });

    const formatPhase = (duration, phase) => {
        const { locale, t } = i18;

        const formatNumber = (number) => {
            const lastDigit = number.toString().charAt(number.length - 1);
            switch (lastDigit) {
                case "1":
                    return number + "st";
                case "2":
                    return number + "nd";
                case "3":
                    return number + "rd";
                default:
                    return number + "th";
            }
        };

        return locale.value == "zh"
            ? `${duration} ${t("year")} (${t("donation.payment")}${phase})`
            : `${duration} ${t("year")} (${formatNumber(phase)} ${t(
                  "donation.payment"
              )})`;
    };

    return {
        currentLocale,
        changeLocale,
        currentLocaleLabel,
        localeOptions,
        i18,
        formatPhase,
    };
}
