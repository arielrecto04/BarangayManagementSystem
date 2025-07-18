<script setup>
import { AuthLayout } from "@/Layouts";
import { Header } from '@/Components';
</script>

<template>
  <AuthLayout>
    <Header></Header>
    <div class="m-3 flex flex-col">
      <!-- Resident Manager -->
      <div class="bg-white p-5 shadow-lg rounded-lg w-1/4">
        <h1 class="uppercase font-bold">Resident Manager</h1>
        <p>Manage and View Resident information</p>
      </div>

      <!-- Search Residents -->
      <div class="flex bg-white shadow-lg rounded-lg w-300 p-7 mt-0 transform -translate-y-10">
        <div class="shadow-lg p-3 w-1/2">
            <input type="text" placeholder="Search Residents..." name="Search Residents" class="w-full rounded-lg  "/>      
        </div>
            <select class="ml-2 bg-white shadow-lg rounded-lg w-1/8 p-3 text-center" placeholder="Age Range">
                <option value="" disabled selected hidden>Age Range</option>
                <!-- Age Range -->
                <option>{{  }}</option> 
                <option>{{  }}</option>
                <option>{{  }}</option>
            </select>
             <select class="flex items-center ml-2 bg-white shadow-lg rounded-lg w-1/8 p-3 text-center " placeholder="Age Range">
                <option value="" disabled selected hidden>Sex</option>
                <!-- Gender -->
                <option>Male</option> 
                <option>Female</option>
            </select>
            <div class="w-1/4 max-w-sm mx-auto ml-2">
                <!-- Route of Add Resident -->
                <router-link to="{{}}" class="block"> 
                    <div class="bg-green-700 shadow-md rounded-lg p-3  cursor-pointer ">
                    <div class="text-lg font-bold text-center text-white">➕ Add Resident</div>
                    </div>
                </router-link>
            </div>
        </div>  
    </div>
    <div class="bg-white rounded-lg shadow-lg p-5 ">
        <h1>Name</h1>
    </div>
        
    
    
  </AuthLayout>
</template>
