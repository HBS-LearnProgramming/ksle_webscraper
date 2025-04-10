<template>
    <Container class="h-screen">
        <h1 class="title">Stocks options form</h1>
        <a-form 
            ref="formRef"
            :model="form"
            layout="vertical"
            @submit="handleSubmit"
            class="space-y-3 max-w-96"
        >
            <a-form-item
                field="name"
                label="Name"
                required
                validate-trigger="false"
            >
                <a-input v-model="form.name" placeholder="Enter the stock name"></a-input>
            </a-form-item>

            <a-form-item
                field="href"
                label="Link"
                required
                validate-trigger="false"
            >
                <a-input v-model="form.href" placeholder="Enter the stock access link"></a-input>
            </a-form-item>
            
            <a-button
                class="w-full h-9 text-sm font-medium transition-all duration-200 hover:scale-[1.02]"
                html-type="submit"
                type="primary"
            >
            <icon-file /> Save
            </a-button>
        </a-form>
        <div class="pb-10">
            <h1 class="title mt-10">Stocks List</h1>
            <div class="filter_row">
                <icon-filter size="20" />
            
                <div class="pl-2" >
                    <a-space direction="vertical" class="filter-margin" v-for="[key, options] in Object.entries(filter_data)" :key="key">
                        <a-select 
                            class="filter-input"
                            v-model="formfilter[key]" 
                            @search="(value) => handleSearch(key, value)"
                            @change="handleFilter(key)" 
                            :placeholder="`Filter ${key}`" 
                            allow-search
                            allow-clear
                        >
                            
                            <a-option v-for="option in options" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </a-option>

                        </a-select>
                    </a-space>
                </div>
                
                <span @click="handleClearFilter" class="absolute right-0 p-1 border border-red-500 hover:cursor-pointer rounded-md mr-2">
                    <icon-delete size="20" class="text-red-500" />
                </span>
            </div>
            <DefaultTable 
                :listData="list_data" 
                :listColumn="list_column"
                :totalData="pagination"
                @pageChange="handlePageChange"
            >
            </DefaultTable>
        </div>
        
    </Container>
</template>
<script>
import useResponsive from "@/Hooks/useResponsive";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { Message } from "@arco-design/web-vue";
import msMY from "@arco-design/web-vue/es/locale/lang/en-US";
import { onMounted, ref, inject, watch, markRaw } from "vue";
import { useAppStore } from "@/Store";
import axios from "axios";
export default {
    props: [
        'stock',
        'pagination',
        'list_data',
        'filter_data',
    ],
    setup(props) {
        useResponsive();
        const appStore = useAppStore();
        const isMobile = appStore.isMobile;
        const swal = inject("$swal");
        const list_data = ref(props.list_data.data);
        const list_column = ref(props.list_data.columns);
        const filter_data = ref(props.filter_data);
        const form = useForm({
            id: props.stock?.id || null,
            name: props.stock?.name || null, 
            href: props.stock?.href || null,
        });

        const pagination = ref({
            current: props.pagination.current || 1,
            pageSize: props.pagination.perPage || 20,
            total: props.pagination.total || 0,
        });
        
        const handleSubmit = async () =>{
            
            try {
                swal.fire({
                    title: "Saving...",
                    text: "Please wait a minute...",
                    didOpen: () => {
                        swal.showLoading();
                    },
                });
                const response = await axios.post('/stock', form, {
                    headers: { "Content-Type": "multipart/form-data" },
                    responseType: 'blob' // Ensure binary data for file download
                });

                if (response.status === 200) {
                    Message.success(response?.data?.message);
                    swal.close();
                    router.visit('/stock');
                }
               
            } catch (error) {
                console.log('Error: ', error);
                swal.close();
                Message.error("Saving new stock failed");
            }
        }

        const formfilter = useForm(
            list_column.value.reduce((acc, column) => {
                acc[column.dataIndex] = null;
                return acc;
            }, {})
        );

        function callProps(){
            list_data.value = props.list_data.data
            list_column.value = props.list_data.columns
            filter_data.value = props.filter_data

            pagination.value = {
                current: props.pagination.current || 1,
                pageSize: props.pagination.perPage || 20,
                total: props.pagination.total || 0,
            };
            console.log('props.pagination: ', props.pagination);
            console.log('pagination: ',pagination.value);
        }
        async function handleFilter(dataIndex){
            const path = window.location.pathname;
            pagination.value.current = 1;
            let queryParams = new URLSearchParams(window.location.search)
            
            if(formfilter[dataIndex] && formfilter[dataIndex].trim() != ""){
                queryParams.set(dataIndex, formfilter[dataIndex].trim());
                queryParams.set('page', 1);
            } else {
                queryParams.delete(dataIndex);
            }
            router.visit(path,{
                data: Object.fromEntries(queryParams),
                preserveState: true,
                preserveScroll: true, 
                replace: true,
                onSuccess: () => {
                    callProps();
                }
            });
        }; 
       
        async function handleClearFilter(){
            const path = window.location.pathname;
            formfilter.reset();
            console.log(pagination.value);
            swal.fire({
                title: "Clearing...",
                timer: 1000,
                didOpen: () => {
                    swal.showLoading();
                },
            });
            router.visit(path,{
                preserveState: true,
                preserveScroll: true, 
                replace: true,
                onSuccess: () => {
                    
                    callProps();
                    swal.close();
                }
            });

        }
        async function handleSearch(key, value){
            const path = window.location.pathname;
            pagination.value.current = 1;
            let queryParams = new URLSearchParams(window.location.search)

            if (value && value.trim() !== "") {
                queryParams.set(key, value.trim());
                queryParams.set('page',1);
            } else {
                queryParams.delete(key);
            }
            router.visit(path,{
                data: Object.fromEntries(queryParams),
                preserveState: true,
                preserveScroll: true, 
                replace: true,
                onSuccess: () => {
                    callProps();
                }
            });
            
        }

        function handlePageChange(page) {
            console.log('page: ', page);
            pagination.value.current = page;
            let queryParams = new URLSearchParams(window.location.search);
            queryParams.set("page", page);
            const path = window.location.pathname;
           
            router.visit(path, {
                data: Object.fromEntries(queryParams),
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onSuccess: () => {
                    callProps();
                }
            });
        }
        
       

        return {
            list_data,
            filter_data,
            list_column,
            handleFilter,
            handleSearch,
            handleClearFilter,
            handleSubmit,
            form,
            formfilter,
            pagination,
            handlePageChange
        };
    },
};
</script>