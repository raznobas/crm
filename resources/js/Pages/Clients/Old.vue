<script setup>

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import 'dayjs/locale/ru';
import { useToast } from "@/useToast";
const { showToast } = useToast();

dayjs.extend(relativeTime);

import {Head} from "@inertiajs/vue3";
import ClientModal from "@/Components/ClientModal.vue";
import {ref} from "vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps(['oldClients']);

const showModal = ref(false);
const selectedClient = ref(null);

const openModal = async (clientId) => {
    try {
        selectedClient.value = (await axios.get(route('clients.show', clientId))).data;
        showModal.value = true;
    } catch (error) {
        showToast("Ошибка получения данных: " + error.message, "error");
    }
};
const handleClientUpdated = (updatedClient) => {
    // Обновляем данные о клиенте после того как с дочернего компонента пришел emit после обновления данных
    selectedClient.value = updatedClient;
};

const closeModal = () => {
    showModal.value = false;
    selectedClient.value = null;
};
</script>

<template>
    <Head title="Старые клиенты"/>

    <AuthenticatedLayout>
        <div class="mx-auto p-4 sm:p-6 lg:p-8">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Список старых клиентов</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Фамилия
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата
                        рождения
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Телефон
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Почта
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Истек
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Действия
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="client in oldClients.data" :key="client.id">
                    <td class="px-3 py-2 whitespace-nowrap">{{ client.id }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ client.surname }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ client.name }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ client.birthdate ? dayjs(client.birthdate).format('DD.MM.YYYY') : '' }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ client.phone }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ client.email }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <span class="cursor-help"
                              :title="client.subscription_end_date ? dayjs(client.subscription_end_date).format('DD.MM.YYYY') : ''">
                            {{
                                client.subscription_end_date ? dayjs(client.subscription_end_date).locale('ru').fromNow() : ''
                            }}
                        </span>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <button @click="openModal(client.id)" class="text-indigo-600 hover:text-indigo-900">Карточка
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
            <Pagination :items="oldClients"/>
            <ClientModal :show="showModal" :client="selectedClient"
                         @close="closeModal" @client-updated="handleClientUpdated"/>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
