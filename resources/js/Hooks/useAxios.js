import axios from "axios";

export const useAxios = () => {
    const http = axios.create();

    http.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

    return http;
};
