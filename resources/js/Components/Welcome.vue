<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import { ref, watchEffect } from "vue";

const jobs = ref([]);
const form = useForm({
  pdfs: null,
  file: null,
  periodo: null,
});

function submit() {
  form.post("/send-pdf");
}

function jobsFailed() {
  axios.get("/jobs-failed").then((response) => {
    jobs.value = response.data;

    console.log(jobs);
  });
}

watchEffect(() => {
  jobsFailed();
});
</script>

<template>
    <div>
        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

            <h1 class="mt-8 text-2xl font-medium text-gray-900">
                Bienvenido a tu apliacion para enviar PDF a varios usuarios
            </h1>

            <p class="mt-6 text-gray-500 leading-relaxed">
            <h4>Para enviar los pdf sigues los siguientes pasos</h4>
            <ul>
                <li>1. Cargue todos los pdf que quiera enviar (Solo recibos de pago KFC con el formato vigente el 12-05-2024). Se recomienda una cantidad maxima de 30 por lote</li>
                <li>2. Crea un excel sin la cabecera, en la primera columna debes colocar la cedula del empleado y en el segundo, el correo al que lo quieres enviar</li>
                <li>3. coloque el perido actual</li>
                <li>4. Presiona el boton enviar</li>
            </ul>

            <p>Nota: Cada ves que envie un lote los pdf y las tareas fallidas se borraran.</p>
            </p>
        </div>

        <div class="bg-gray-200 bg-opacity-25">
            <div class="flex justify-center pt-10 pb-10">

                <form class="max-w-sm mx-auto" @submit.prevent="submit">


                    <div class="mb-2 mt-2">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            for="multiple_files">Cargue los PDF</label>
                        <input @input="form.pdfs = $event.target.files"
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                            id="multiple_files" type="file" multiple accept="application/pdf">
                        <InputError class="mt-2" :message="form.errors.pdfs" />
                    </div>


                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            for="user_avatar">Cargar
                            excel</label>
                        <input @input="form.file = $event.target.files[0]"
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                            aria-describedby="user_avatar_help" id="user_avatar" type="file" accept=".xlsx, .xls">

                        <InputError class="mt-2" :message="form.errors.file" />
                    </div>

                    <div>

                        <label for="pdfs" class="block mb-2 mt-2 text-sm font-medium text-gray-900 dark:text-white">
                            Ingrese el Periodo</label>
                        <input v-model="form.periodo" type="text" id="periodo"
                            aria-describedby="helper-text-explanation"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Periodo : 3 Año: 2023">

                        <InputError class="mt-2" :message="form.errors.periodo" />
                    </div>



                    <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                        {{ form.progress.percentage }}%
                    </progress>


                    <button type="submit"
                        class="mt-2 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Enviar</button>
                </form>

            </div>

            <div v-if="jobs.length > 0">
                <h1 class="mt-8 text-2xl font-medium text-gray-900 text-center">
                    Correos que no se pudieron enviar
                </h1>


                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 text-center">
                        <thead class="text-xs text-gray-900 uppercase dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Cedula
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Correo
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white dark:bg-gray-800" v-for="({cedula, email}, index) in jobs" :key="index">
                                <th scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ cedula }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ email }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</template>
