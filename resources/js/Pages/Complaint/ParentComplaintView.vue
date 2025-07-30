<script setup>
import { AuthLayout } from "@/Layouts";
import { useRouter } from "vue-router";
import { PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline'
const router = useRouter();
</script>

<template>
    <AuthLayout>
        <div class="m-3 flex flex-col">
            <!-- Blotter Manager -->
            <div class="bg-white p-5 shadow-lg rounded-lg w-1/4">
                <h1 class="uppercase font-bold">Blotter Manager</h1>
                <p>Manage and View Blotter information</p>
            </div>

            <!-- Search Blotters -->
            <div class="flex bg-white shadow-lg rounded-lg p-7 mt-0 transform -translate-y-10">
                <div class="shadow-lg p-3 w-1/2">
                    <form>
                        <input type="text" placeholder="Search Blotters..." name="Search Blotters"
                            class="w-full rounded-lg  ">
                    </form>
                </div>
                <select class="ml-2 bg-white shadow-lg rounded-lg w-1/8 p-3 text-center" placeholder="Case Type">
                    <option value="" disabled selected hidden>Case Type</option>
                    <option>Theft</option>
                    <option>Assault</option>
                    <option>Other</option>
                </select>
                <select class="flex items-center ml-2 bg-white shadow-lg rounded-lg w-1/8 p-3 text-center "
                    placeholder="Status">
                    <option value="" disabled selected hidden>Status</option>
                    <option>Open</option>
                    <option>In Progress</option>
                    <option>Resolved</option>
                </select>
                <div class="w-1/4 max-w-sm mx-auto ml-2">
                    <!-- Route of Add Blotter -->
                    <template v-if="router.currentRoute.value.path == '/blotter/list-blotter'">
                       <router-link to="/blotter/add-blotter" class="block">
  <div class="bg-green-700 shadow-md rounded-lg p-3 cursor-pointer flex justify-center items-center">
    <div class="flex items-center gap-2 text-lg font-bold text-white">
      <PlusIcon class="w-6 h-6" /> Add Blotter
    </div>
  </div>
</router-link>
                    </template>
                    <template v-else>
                        <router-link to="/blotter" class="block">
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