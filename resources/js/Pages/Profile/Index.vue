<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
defineOptions({
    layout: MainLayout,
});

import { useForm, usePage } from "@inertiajs/vue3";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const page = usePage();
const user = computed(() => page.props.user);

const form = useForm({
    birthday: user.value.birthday,
    gender: user.value.gender || "",
    weight: user.value.weight,
    height: user.value.height,
    diseases: user.value.diseases,
});

const pressureForm = useForm({
    systolic: null,
    diastolic: null,
    date: null,
});

const submit = () =>
    form.patch(route("profile.update"), {
        onSuccess: () => {
            const toastMagic = new ToastMagic();
            toastMagic.success("Gotowe!", "Twoje dane zostały zapisane.");
        },
    });
const submitPressure = () =>
    pressureForm.post(route("blood.pressure"), {
        onSuccess: () => {
            pressureForm.reset();
            const toastMagic = new ToastMagic();
            toastMagic.success(
                "Gotowe!",
                "Wyniki pomiaru ciśnienia zostały zapisane."
            );
        },
    });

const props = defineProps({
    blood_pressures: Array,
    files: Array,
});

const formatDate = (myDate, monthFormat = "long") => {
    let customDate = new Date(myDate);
    return customDate.toLocaleDateString("pl-PL", {
        day: "numeric",
        month: monthFormat,
        year: "numeric",
    });
};
</script>

<template>
    <Head>
        <title>Profil</title>
    </Head>
    <div class="columns-1 lg:columns-2 gap-6 break-inside-avoid">
        <!-- Podstawowe dane -->
        <div
            class="flex flex-col gap-6 bg-white rounded-2xl shadow-xs p-6 mb-6 break-inside-avoid"
        >
            <div class="flex flex-col gap-2">
                <div class="flex gap-2 items-center">
                    <div
                        class="flex justify-center items-center bg-blue-200 size-12 rounded-2xl"
                    >
                        <i class="fa-solid fa-user text-blue-600 text-xl"></i>
                    </div>
                    <h4 class="text-2xl font-normal">Podstawowe dane</h4>
                </div>
                <span class="text-sm text-gray-600"
                    >W tym miejscu możesz wyprowadzic lub zaktualizowac swoje
                    dane pacjenta.</span
                >
            </div>

            <form @submit.prevent="submit" class="flex flex-wrap gap-4">
                <div class="w-full flex flex-wrap gap-4">
                    <div
                        class="flex flex-col gap-1 w-full lg:w-[calc(50%-8px)] text-sm"
                    >
                        <label for="birthday" class="text-gray-600 text-xs"
                            >Data urodzenia</label
                        >
                        <input
                            type="date"
                            placeholder="Twoja waga"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                            v-model="form.birthday"
                        />
                        <span
                            class="text-red-500 text-xs"
                            v-if="form.errors.birthday"
                            >{{ form.errors.birthday }}</span
                        >
                    </div>

                    <div
                        class="flex flex-col gap-1 w-full lg:w-[calc(50%-8px)] text-sm"
                    >
                        <label for="gender" class="text-gray-600 text-xs"
                            >Płeć</label
                        >
                        <select
                            id="gender"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                            v-model="form.gender"
                        >
                            <option value="">Wybierz płec</option>
                            <option value="Kobieta">Kobieta</option>
                            <option value="Mężczyzna">Mężczyzna</option>
                            <option value="Wolę nie podawać">
                                Wolę nie podawać
                            </option>
                        </select>
                        <span
                            class="text-red-500 text-xs"
                            v-if="form.errors.gender"
                            >{{ form.errors.gender }}</span
                        >
                    </div>
                </div>

                <div class="w-full flex flex-wrap gap-4">
                    <div
                        class="flex flex-col gap-1 w-full lg:w-[calc(50%-8px)] text-sm"
                    >
                        <label for="weight" class="text-gray-600 text-xs">
                            Waga [kg]
                        </label>
                        <input
                            type="number"
                            placeholder="Twoja waga"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                            v-model="form.weight"
                        />
                        <span
                            class="text-red-500 text-xs"
                            v-if="form.errors.weight"
                            >{{ form.errors.weight }}</span
                        >
                    </div>
                    <div
                        class="flex flex-col gap-1 w-full lg:w-[calc(50%-8px)] text-sm"
                    >
                        <label for="height" class="text-gray-600 text-xs">
                            Wzrost [cm]
                        </label>
                        <input
                            type="number"
                            placeholder="Twój wzrost"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                            v-model="form.height"
                        />
                        <span
                            class="text-red-500 text-xs"
                            v-if="form.errors.height"
                            >{{ form.errors.height }}</span
                        >
                    </div>
                </div>

                <div class="w-full flex flex-wrap gap-4">
                    <div class="w-full flex">
                        <div class="flex flex-col gap-1 w-full text-sm">
                            <label for="diseases" class="text-gray-600 text-xs"
                                >Stwierdzone choroby</label
                            >
                            <textarea
                                type="text"
                                placeholder="Np. cukrzyca, otyłość, nadciśnienie tętnicze..."
                                class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm resize-none h-28"
                                v-model="form.diseases"
                            ></textarea>
                            <span
                                class="text-red-500 text-xs"
                                v-if="form.errors.diseases"
                                >{{ form.errors.diseases }}</span
                            >
                        </div>
                    </div>
                </div>

                <div class="w-full">
                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        :type="'submit'"
                        >Zapisz</PrimaryButton
                    >
                </div>
            </form>
        </div>

        <!-- Dokumentacja medyczna -->
        <div
            class="flex flex-col gap-6 bg-white rounded-2xl shadow-xs border-slate-300 p-6 mb-6 break-inside-avoid"
        >
            <div class="flex flex-col gap-2">
                <div class="flex gap-2 items-center">
                    <div
                        class="flex justify-center items-center bg-blue-200 size-12 rounded-2xl"
                    >
                        <i
                            class="fa-solid fa-folder-open text-blue-600 text-xl"
                        ></i>
                    </div>
                    <h4 class="text-2xl font-normal">Dokumentacja medyczna</h4>
                </div>
                <span class="text-sm text-gray-600"
                    >W tym miejscu mozesz przesłac pliki twojej dokumentacji
                    medycznej.</span
                >
            </div>
            <div
                v-for="file in files"
                :key="file.id"
                class="flex gap-4 items-start mt-4 w-full border-b border-gray-200 pb-4 last:border-none"
            >
                <!-- Ikona -->
                <div
                    class="shrink-0 size-12 rounded-2xl bg-gray-100 flex justify-center items-center"
                >
                    <i
                        v-if="file.type == 'doc'"
                        class="fa-solid fa-file text-blue-600 text-xl"
                    ></i>
                    <i
                        v-if="file.type == 'pdf'"
                        class="fa-solid fa-file-pdf text-blue-600 text-xl"
                    ></i>
                </div>

                <!-- Nazwa i rozmiar -->
                <div class="grow flex flex-col overflow-hidden">
                    <Link
                        :href="route('file.show', file.id)"
                        class="text-base font-normal mb-0 break-words"
                    >
                        {{ file.filename }}
                    </Link>
                    <span class="text-xs text-gray-600"
                        >{{ file.size }} MB</span
                    >
                </div>

                <!-- Data -->
                <div class="w-[100px] text-sm text-gray-600 text-right">
                    {{ formatDate(file.created_at, "long") }}
                </div>

                <!-- Opcje -->
                <div class="ml-2 shrink-0">
                    <button class="text-gray-600">
                        <i class="fa-solid fa-ellipsis"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Pomiar ciśnienia -->
        <div
            class="flex flex-col gap-6 bg-white rounded-2xl shadow-xs border-slate-300 p-6 break-inside-avoid"
        >
            <div class="flex flex-col gap-2">
                <div class="flex gap-2 items-center">
                    <div
                        class="flex justify-center items-center bg-blue-200 size-12 rounded-2xl"
                    >
                        <i
                            class="fa-solid fa-heart-pulse text-blue-600 text-xl"
                        ></i>
                    </div>
                    <h4 class="text-2xl font-normal">Pomiar ciśnienia</h4>
                </div>
                <span class="text-sm text-gray-600"
                    >W tym miejscu mozesz wprowadzac swoje ostatnie pomiary
                    ciśnienia krwi.</span
                >
            </div>

            <form
                @submit.prevent="submitPressure"
                method="POST"
                class="flex flex-wrap gap-4"
            >
                <div class="w-full flex flex-wrap gap-4">
                    <div
                        class="flex flex-col gap-1 w-full lg:w-[calc(33%-12px)] text-sm"
                    >
                        <label for="date" class="text-xs text-gray-600"
                            >Data pomiaru</label
                        >
                        <input
                            type="date"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                            v-model="pressureForm.date"
                        />
                        <span
                            class="text-red-500 text-xs"
                            v-if="pressureForm.errors.date"
                            >{{ pressureForm.errors.date }}</span
                        >
                    </div>
                    <div
                        class="flex flex-col gap-1 w-full lg:w-[calc(33%-12px)] text-sm"
                    >
                        <label for="systolic" class="text-xs text-gray-600"
                            >Ciśnienie skurczowe</label
                        >
                        <input
                            type="number"
                            placeholder="Np. 120"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                            v-model="pressureForm.systolic"
                        />
                        <span
                            class="text-red-500 text-xs"
                            v-if="pressureForm.errors.systolic"
                            >{{ pressureForm.errors.systolic }}</span
                        >
                    </div>

                    <div
                        class="flex flex-col gap-1 w-full lg:w-[calc(33%-12px)] text-sm"
                    >
                        <label for="diastolic" class="text-xs text-gray-600"
                            >Ciśnienie rozkurczowe</label
                        >
                        <input
                            type="number"
                            v-model="pressureForm.diastolic"
                            placeholder="Np. 80"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                        />
                        <span
                            class="text-red-500 text-xs"
                            v-if="pressureForm.errors.diastolic"
                            >{{ pressureForm.errors.diastolic }}</span
                        >
                    </div>
                </div>
                <PrimaryButton
                    :class="{ 'opacity-25': pressureForm.processing }"
                    :disabled="pressureForm.processing"
                    :type="'submit'"
                    >Dodaj</PrimaryButton
                >
            </form>

            <div class="flow-root">
                <h4 class="text-xl font-normal mb-1">Historia pomiarów</h4>
                <div class="inline-block min-w-full align-middle">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th
                                    scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0"
                                >
                                    Data
                                </th>
                                <th
                                    scope="col"
                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                >
                                    Ciśnienie krwi
                                </th>
                                <th
                                    scope="col"
                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                >
                                    Opinia
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="pressure in blood_pressures"
                                :key="pressure.id"
                            >
                                <td
                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0"
                                >
                                    {{ formatDate(pressure.date, "numeric") }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                                >
                                    {{ pressure.systolic }} /
                                    {{ pressure.diastolic }}
                                </td>
                                <td
                                    class="px-3 py-4 text-sm text-gray-500 break-words"
                                >
                                    {{ pressure.review }}
                                </td>
                            </tr>

                            <!-- More people... -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
