<template>
    <Container class="h-screen">
        <h1 class="title">KSLE Web Scraper</h1>
        <a-form 
            ref="formRef"
            :model="form"
            layout="vertical"
            @submit="handleSubmit"
            class="space-y-3 max-w-96"
        >
            <a-form-item
                field="stocks"
                label="Stocks"
                required
                validate-trigger="false"
            >
                <a-select v-model="form.stocks" placeholder="Select the stock for scrap" multiple allow-clear>
                    <a-option v-for="option in options" :value="option.href">{{ option.text }}</a-option>
                </a-select>
                <a-button class="ml-2" type="outline" @click="handleClickAll()">Select all</a-button>
            </a-form-item>

            <a-form-item
                    field="file"
                    label="Sample Excel File"
                    required
                    validate-trigger="false"
                >
                    <a-upload
                        :before-upload="handleBeforeUpload"
                        :auto-upload="false"
                        :limit="1"
                        accept=".xlsx,xls,csv"
                        @change="onChange"
                        :show-link="true"
                        class="w-full"
                    >
                        <template #upload-button>
                            <a-button class="w-full">
                                <template #icon>
                                    <icon-upload />
                                </template>
                                Upload Excel File
                            </a-button>
                        </template>
                        <template #file-name>
                            <span class="text-sm text-gray-600">
                                {{
                                    form.file?.name?.length >
                                    30
                                        ? form.file.name.substring(
                                                0,
                                                30
                                            ) + "..."
                                        : form.file?.name
                                }}
                            </span>
                        </template>
                    </a-upload>
                   
            </a-form-item>
            
            <a-button
                class="w-full h-9 text-sm font-medium transition-all duration-200 hover:scale-[1.02]"
                html-type="submit"
                type="primary"
            >
            <icon-file /> Export
            </a-button>
        </a-form>
        
    </Container>
</template>
<script>
import useResponsive from "@/Hooks/useResponsive";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { Message } from "@arco-design/web-vue";
import msMY from "@arco-design/web-vue/es/locale/lang/en-US";
import { onMounted, ref, inject, watch, markRaw } from "vue";
import { useAppStore } from "@/Store";
import { IconUser, IconStorage, IconDashboard, IconMessage, IconRefresh, IconImport, IconBookmark } from '@arco-design/web-vue/es/icon';
import axios from "axios";
export default {
    props: ['data'],
    setup(props) {
        useResponsive();
        const appStore = useAppStore();
        const isMobile = appStore.isMobile;
        const options = ref(props.data);
        const swal = inject("$swal");
        const form = useForm({
            stocks: [], 
            file: null,
        });
        
        const handleSubmit = async () =>{
            
            const formData = new FormData();
            formData.append("stocks", JSON.stringify(form.stocks)); // Convert array to string
            formData.append("file", form.file); // Append file
            
            try {
                swal.fire({
                    title: "Scraping...",
                    didOpen: () => {
                        swal.showLoading();
                    },
                });
                const response = await axios.post('/', formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                    responseType: 'blob' // Ensure binary data for file download
                });

                if (response.status === 200) {
                    const now = new Date();
                    const formattedDate = now.toISOString().slice(0, 10).replace(/-/g, '_'); // e.g. 2025_04_02
                    const filename = `${formattedDate}_KSLE_Scraper.xlsx`;
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', filename);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    swal.close();
                }
               
            } catch (error) {
                console.log('Error: ', error);
                Message.error("Failed to download file");
            }
        }
        
        const handleBeforeUpload = (file) => {
            form.file = file;
            return false;
        }
        const handleClickAll = () => {
            const hrefValues = options.value.map(option => option.href);
            form.stocks = hrefValues;
        }
        const onChange = (fileList) => {
            form.file = fileList.length > 0 ? fileList[0].file : null;
        }

        return {
            form,
            options,
            handleSubmit,
            handleBeforeUpload,
            handleClickAll,
            onChange
        };
    },
};


</script>