<script setup>


</script>

<template>
 <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
    <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-5xl">
      <h1 class="text-2xl font-bold mb-6">Add New Resident</h1>
      <h2 class="text-lg font-semibold mb-4">Resident Profile</h2>

      <div class="grid grid-cols-3 gap-4">
        <!-- First Row -->
        <input type="text" placeholder="First Name" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
        <input type="text" placeholder="Last Name" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
        <div class="row-span-3 flex justify-center items-center">
          <div class="w-50 h-50 bg-gray-200 rounded-md"></div>
        </div>

        <!-- Second Row -->
        <input type="date" placeholder="Birthday" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
        <input type="text" placeholder="Age" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
        <input type="text" placeholder="Gender" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />

        <!-- Third Row -->
        <input type="text" placeholder="Lot no. / Street / Sub Division / Address" class="input-style col-span-2 border border-gray-200 rounded-md px-4 py-2" />
        <button class="input-style bg-white  col-span-1 border border-gray-200 rounded-md px-4 py-2">Add Image</button>

        <!-- Fourth Row -->
        <input type="text" placeholder="Contact No." class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
        <input type="text" placeholder="Family Member" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
        <input type="text" placeholder="Emergency Contact" class="input-style col-span-1 border border-gray-200 rounded-md px-4 py-2" />
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-center mt-10 gap-4">
        <form action="" method="post" >
          <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl shadow-md">Save</button>
          <router-link to="/dashboard/residents" class="bg-white px-6 py-2 rounded-xl shadow-xl ml-4 font-bold hover:bg-gray-200">Cancel</router-link>
        </form>
      </div>
    </div>
  </div>
</template>
