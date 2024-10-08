<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";
import {Head, useForm, usePage} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {ref} from "vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Pagination from "@/Components/Pagination.vue";
import ClientModal from "@/Components/ClientModal.vue";
import dayjs from "dayjs";
import { useToast } from "@/useToast";
const { showToast } = useToast();

const form = useForm({
    surname: null,
    name: null,
    patronymic: null,
    birthdate: null,
    workplace: null,
    phone: null,
    email: null,
    telegram: null,
    instagram: null,
    address: null,
    gender: 'male',
    ad_source: null,
    is_lead: false,
    director_id: usePage().props.auth.director_id,
});

const props = defineProps(['clients', 'source_options']);

const submit = () => {
    form.post(route('clients.store'), {
        onSuccess: () => {
            form.reset();
            showToast("Клиент успешно добавлен!", "success");
        },
        onError: (errors) => {
            Object.values(errors).forEach(error => {
                showToast(error, "error");
            });
        },
    });
};
const handleClientUpdated = (updatedClient) => {
    // Обновляем данные о клиенте после того как с дочернего компонента пришел emit после обновления данных
    selectedClient.value = updatedClient;
};
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

const closeModal = () => {
    showModal.value = false;
    selectedClient.value = null;
};

</script>

<template>
    <Head title="Клиенты"/>

    <AuthenticatedLayout>
        <div class="mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="submit">
                <div class="flex flex-row flex-wrap gap-2 items-end">
                    <div class="flex flex-col">
                        <label for="surname" class="text-sm font-medium text-gray-700">Фамилия</label>
                        <input id="surname" type="text" v-model="form.surname" class="mt-1 p-1 border border-gray-300 rounded-md"/>
                        <InputError :message="form.errors.surname" class="mt-2 text-sm text-red-600"/>
                    </div>
                    <div class="flex flex-col">
                        <label for="name" class="text-sm font-medium text-gray-700">Имя<span class="text-red-600">*</span></label>
                        <input id="name" type="text" required v-model="form.name" class="mt-1 p-1 border border-gray-300 rounded-md"/>
                        <InputError :message="form.errors.name" class="mt-2 text-sm text-red-600"/>
                    </div>
                    <div class="flex flex-col">
                        <label for="patronymic" class="text-sm font-medium text-gray-700">Отчество</label>
                        <input id="patronymic" type="text" v-model="form.patronymic" class="mt-1 p-1 border border-gray-300 rounded-md"/>
                        <InputError :message="form.errors.patronymic" class="mt-2 text-sm text-red-600"/>
                    </div>
                    <div class="flex flex-col col-span-1 w-32">
                        <label for="birthdate" class="text-sm font-medium text-gray-700">Дата рождения</label>
                        <input id="birthdate" type="date" v-model="form.birthdate" class="mt-1 p-1 border border-gray-300 rounded-md"/>
                        <InputError :message="form.errors.birthdate" class="mt-2 text-sm text-red-600"/>
                    </div>
                    <div class="flex flex-col">
                        <label for="workplace" class="text-sm font-medium text-gray-700">Место работы</label>
                        <input id="workplace" type="text" v-model="form.workplace" class="mt-1 p-1 border border-gray-300 rounded-md"/>
                        <InputError :message="form.errors.workplace" class="mt-2 text-sm text-red-600"/>
                    </div>
                    <div class="flex flex-col">
                        <label for="phone" class="text-sm font-medium text-gray-700">Телефон</label>
                        <input id="phone" type="text" v-model="form.phone" class="mt-1 p-1 border border-gray-300 rounded-md"/>
                        <InputError :message="form.errors.phone" class="mt-2 text-sm text-red-600"/>
                    </div>
                    <div class="flex flex-col">
                        <label for="email" class="text-sm font-medium text-gray-700">Почта</label>
                        <input id="email" type="text" v-model="form.email" class="mt-1 p-1 border border-gray-300 rounded-md"/>
                        <InputError :message="form.errors.email" class="mt-2 text-sm text-red-600"/>
                    </div>
                    <div class="flex flex-col">
                        <label for="telegram" class="text-sm font-medium text-gray-700">Телеграм</label>
                        <input id="telegram" type="text" v-model="form.telegram" class="mt-1 p-1 border border-gray-300 rounded-md"/>
                        <InputError :message="form.errors.telegram" class="mt-2 text-sm text-red-600"/>
                    </div>
                    <div class="flex flex-col">
                        <label for="instagram" class="text-sm font-medium text-gray-700">Инстаграм</label>
                        <input id="instagram" type="text" v-model="form.instagram" class="mt-1 p-1 border border-gray-300 rounded-md"/>
                        <InputError :message="form.errors.instagram" class="mt-2 text-sm text-red-600"/>
                    </div>
                    <div class="flex flex-col">
                        <label for="address" class="text-sm font-medium text-gray-700">Адрес</label>
                        <input id="address" type="text" v-model="form.address" class="mt-1 p-1 border border-gray-300 rounded-md"/>
                        <InputError :message="form.errors.address" class="mt-2 text-sm text-red-600"/>
                    </div>
                    <div class="flex flex-col w-14">
                        <label for="gender" class="text-sm font-medium text-gray-700">Пол</label>
                        <select id="gender" v-model="form.gender" class="mt-1 p-1 border border-gray-300 rounded-md">
                            <option value="male">М</option>
                            <option value="female">Ж</option>
                        </select>
                        <InputError :message="form.errors.gender" class="mt-2 text-sm text-red-600"/>
                    </div>
                    <div class="flex flex-col">
                        <label for="ad_source" class="text-sm font-medium text-gray-700">Источник</label>
                        <select id="ad_source" v-model="form.ad_source" class="mt-1 p-1 pe-8 border border-gray-300 rounded-md">
                            <option v-for="source in source_options.filter(c => c.type === 'ad_source')"
                                    :value="source.name" :key="source.id">{{ source.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.ad_source" class="mt-2 text-sm text-red-600"/>
                    </div>
                </div>
                <div class="mt-4">
                    <PrimaryButton :disabled="form.processing">Добавить клиента</PrimaryButton>
                    <SecondaryButton class="ml-2" type="button" @click="form.reset()">Очистить</SecondaryButton>
                </div>
            </form>
            <h3 class="mt-8 mb-4 text-lg font-medium text-gray-900">Список клиентов вашей организации</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Фамилия</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Отчество</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата рождения</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Телефон</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Почта</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="client in clients.data" :key="client.id">
                    <td class="px-3 py-2 whitespace-nowrap">{{ client.id }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ client.surname }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ client.name }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ client.patronymic }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ client.birthdate ? dayjs(client.birthdate).format('DD.MM.YYYY') : '' }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ client.phone }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ client.email }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <button @click="openModal(client.id)" class="text-indigo-600 hover:text-indigo-900">Карточка</button>
                    </td>
                </tr>
                </tbody>
            </table>
            <Pagination :items="clients" />
            <ClientModal :show="showModal" :client="selectedClient"
                         @close="closeModal" @client-updated="handleClientUpdated" />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
</style>
