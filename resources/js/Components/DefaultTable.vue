<template>

    <a-table 
        :columns="listColumn"
        :data="listData" 
        :bordered="true" 
        :scrollbar="true"
        :pagination="pagination" 
        @pageChange="handlePageChange"
        :scroll="{x:'auto',y: '200'}">
        <template #action="{record}">
            <a-dropdown trigger="hover" position="bottom" >
                    <div class="w-fit border border-primary px-5 py-1 rounded-sm hover:cursor-pointer"><icon-settings /> Action</div>
                    <template #td>
                        <td class="text-nowrap"></td>
                    </template>
                    <template #content>
                    
                        <a-doption class="hover:text-primary transition-colors" v-for="action in record['action']" @click="handleRoute(action[0],action[2])">{{ action[1] }}</a-doption>
                    
                    </template>
                </a-dropdown>
        </template>
    </a-table>
   
</template>
<script>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { onMounted, ref, inject ,watch, markRaw } from "vue";
export default {
    props: {
        listData:Object,
        listColumn:Object,
        totalData:Object,
    },
    setup(props, {emit}){
        const listColumn = ref(props.listColumn);
        const listData = ref(props.listData);
        const swal = inject("$swal");
        const pagination = ref({
            current: props.totalData?.current || 1,
            pageSize: props.totalData?.pageSize || 20,
            total: props.totalData?.total || 0,
        });
      
        watch(
            () => props.listData,
            (newvalue) => {
                listData.value = newvalue;
            }
        )
        watch(
            () => props.totalData,
            (newvalue) => {
                pagination.value = {
                    current: newvalue.current || 1,
                    pageSize: newvalue.perPage || 20,
                    total: newvalue.total || 0,
                }
            }
        )
        watch(
            () => props.listColumn,
            (newvalue) => {
                listColumn.value = newvalue;
            }
        )
        function handlePageChange(page) {
            pagination.value.current = page;
            
            emit("handlePageChange", page);
        }
        function deletePost(path){
            swal.fire({
                title: 'Are you sure you want to delete?',
                text: "This action cannot be undo.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#262161',
                cancelButtonColor: '#b8b8b8'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        let response = await axios.post(path);
                        swal.fire({
                            title: "Success!",
                            text: response.data.message || "Successfully deleted.",
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false
                        });

                        router.visit(window.location.pathname);
                    } catch (error) {
                        console.log(error);
                        swal.fire({
                            title: "Error!",
                            text: error.response?.data?.message || "An error occurred. Please try again.",
                            icon: "error"
                        });
                    }
                }
            });
        }

        async function signatureGeneratorPost(path) {
            try {
                let response = await axios.post(path);

                swal.fire({
                    title: "Successfully generated the temporary e-signature link!",
                    html: `
                        <a id='signature_link' class='signature_link' href='${response.data.message}' target="_blank">Click to access</a>
                        <button id='copy_button' class='signature_button'>Click to Copy</button>
                    `,
                    icon: "success",
                    showConfirmButton: false,
                    timer: 5000, // Increased timer to give user time to copy
                    didOpen: () => {
                        // Attach event listener when the modal opens
                        document.getElementById('copy_button').addEventListener('click', async function () {
                            let linkValue = document.getElementById('signature_link').href;
                            console.log(linkValue);
                            try {
                                let tempInput = document.createElement("input");
                                tempInput.value = linkValue;
                                document.body.appendChild(tempInput);
                                tempInput.select();
                                document.execCommand("copy");
                                document.body.removeChild(tempInput);
                                swal.fire({
                                    icon: "success",
                                    title: "Copied!",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } catch (error) {
                                console.error("Clipboard write failed:", error);
                            }
                            
                        });
                    }
                });

                router.visit(window.location.pathname);
            } catch (error) {
                console.log(error);
                swal.fire({
                    title: "Error!",
                    text: error.response?.data?.message || "An error occurred. Please try again.",
                    icon: "error"
                });
            }
        }


        async function handleRoute(path,type){
            if(type=='get'){
                if(path.includes('generate_pdf')){
                    window.open(path);
                }else{
                    router.get(path);
                }
                
                
            }else{
                if(path.includes('delete')){
                    deletePost(path);
                }
                else if(path.includes('signature_generation')){
                    signatureGeneratorPost(path)
                }
                else{
                    router.post(path)
                }
                
            }
            
        }

        return {
            handlePageChange,
            pagination,
            listColumn,
            listData,
            handleRoute
            
        }
    },
}
</script>
<style>
.signature_link{
    @apply text-primary mr-2 underline border-0 outline-0;
}
.signature_button{
    @apply border border-solid border-primary text-primary px-2 py-1;
}
</style>