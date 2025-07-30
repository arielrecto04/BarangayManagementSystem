<script setup>
import { AuthLayout } from "@/Layouts";
import { useRouter } from "vue-router";
import { PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline'
const router = useRouter();
</script>

<template>
    <AuthLayout>
        <div class="m-3 flex flex-col">
            <!-- Resident Manager -->
            <div class="bg-white p-5 shadow-lg rounded-lg w-1/4">
                <h1 class="uppercase font-bold">Resident Manager</h1>
                <p>Manage and View Resident information</p>
            </div>

            <!-- Search Residents -->
            <div class="flex bg-white shadow-lg rounded-lg p-7 mt-0 transform -translate-y-10">
                <div class="shadow-lg p-3 w-1/2">
                    <form>
                        <input type="text" placeholder="Search Residents..." name="Search Residents"
                            class="w-full rounded-lg  ">
                    </form>
                </div>
                <select class="ml-2 bg-white shadow-lg rounded-lg w-1/8 p-3 text-center" placeholder="Age Range">
                    <option value="" disabled selected hidden>Age Range</option>
                    <!-- Age Range -->
                    <option>18 - 25</option>
                    <option>26 - 35</option>
                    <option>36 - 45</option>
                </select>
                <select class="flex items-center ml-2 bg-white shadow-lg rounded-lg w-1/8 p-3 text-center "
                    placeholder="Age Range">
                    <option value="" disabled selected hidden>Sex</option>
                    <!-- Gender -->
                    <option>Male</option>
                    <option>Female</option>
                </select>
                <div class="w-1/4 max-w-sm mx-auto ml-2">
                    <!-- Route of Add Resident -->

                    <template v-if="router.currentRoute.value.path == '/residents/list-residents'">
                        <router-link to="/residents/add-resident" class="block">
                            <div class="bg-green-700 shadow-md rounded-lg p-3  cursor-pointer flex justify-center items-center ">
                                <div class="flex items-center gap-2 text-lg font-bold text-center text-white"><PlusIcon class="w-6 h-6" /> Add Resident</div>
                            </div>
                        </router-link>
                    </template>
                    <template v-else>
                        <router-link to="/residents" class="block">
                            <div class="shadow-md rounded-lg p-3  cursor-pointer flex justify-center items-center">
                                <div class="flex items-center gap-2 text-lg font-bold text-center"><XMarkIcon class="w-6 h-6" /> Cancel</div>
                            </div>
                        </router-link>
                    </template>
                </div>
            </div>
        </div>

        <router-view></router-view>


    </AuthLayout>
</template>
