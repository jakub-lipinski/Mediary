<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineOptions({ layout: MainLayout });

defineProps({
    notes: Array,
});

const form = useForm({
    date: new Date().toISOString().slice(0, 10),
    mood: "",
    energy_level: "",
    stress_level: "",
    sleep_hours: "",
    water_intake: "",
    note: "",
});

const submit = () =>
    form.post(route("note.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.date = new Date().toISOString().slice(0, 10);
            const toastMagic = new ToastMagic();
            toastMagic.success("Gotowe!", "Wpis dziennika został zapisany.");
        },
    });

const formatDate = (date) =>
    new Date(date).toLocaleDateString("pl-PL", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
</script>

<template>
    <Head>
        <title>Dziennik</title>
    </Head>

    <div class="columns-1 lg:columns-2 gap-6 break-inside-avoid">
        <div
            class="flex flex-col gap-6 bg-white rounded-2xl shadow p-6 mb-6 break-inside-avoid"
        >
            <div class="flex flex-col gap-2">
                <div class="flex gap-2 items-center">
                    <div
                        class="flex justify-center items-center bg-blue-200 size-12 rounded-2xl"
                    >
                        <i
                            class="fa-solid fa-book-medical text-blue-600 text-xl"
                        ></i>
                    </div>
                    <h4 class="text-2xl font-normal">Nowy wpis</h4>
                </div>
                <span class="text-sm text-gray-600">
                    Zapisz dzisiejsze samopoczucie, sen, stres i nawodnienie.
                </span>
            </div>

            <form @submit.prevent="submit" class="flex flex-wrap gap-4">
                <div class="flex flex-col gap-1 w-full lg:w-[calc(50%-8px)]">
                    <label for="date" class="text-gray-600 text-xs">Data</label>
                    <input
                        id="date"
                        type="date"
                        v-model="form.date"
                        class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                    />
                    <span class="text-red-500 text-xs" v-if="form.errors.date">
                        {{ form.errors.date }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 w-full lg:w-[calc(50%-8px)]">
                    <label for="mood" class="text-gray-600 text-xs"
                        >Nastrój</label
                    >
                    <input
                        id="mood"
                        type="text"
                        v-model="form.mood"
                        placeholder="Np. dobry, spokojny, zmęczony"
                        class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                    />
                    <span class="text-red-500 text-xs" v-if="form.errors.mood">
                        {{ form.errors.mood }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 w-full lg:w-[calc(50%-8px)]">
                    <label for="energy_level" class="text-gray-600 text-xs"
                        >Energia 1-10</label
                    >
                    <input
                        id="energy_level"
                        type="number"
                        min="1"
                        max="10"
                        v-model="form.energy_level"
                        class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                    />
                    <span
                        class="text-red-500 text-xs"
                        v-if="form.errors.energy_level"
                    >
                        {{ form.errors.energy_level }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 w-full lg:w-[calc(50%-8px)]">
                    <label for="stress_level" class="text-gray-600 text-xs"
                        >Stres 1-10</label
                    >
                    <input
                        id="stress_level"
                        type="number"
                        min="1"
                        max="10"
                        v-model="form.stress_level"
                        class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                    />
                    <span
                        class="text-red-500 text-xs"
                        v-if="form.errors.stress_level"
                    >
                        {{ form.errors.stress_level }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 w-full lg:w-[calc(50%-8px)]">
                    <label for="sleep_hours" class="text-gray-600 text-xs"
                        >Sen [h]</label
                    >
                    <input
                        id="sleep_hours"
                        type="number"
                        min="0"
                        max="24"
                        step="0.5"
                        v-model="form.sleep_hours"
                        class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                    />
                    <span
                        class="text-red-500 text-xs"
                        v-if="form.errors.sleep_hours"
                    >
                        {{ form.errors.sleep_hours }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 w-full lg:w-[calc(50%-8px)]">
                    <label for="water_intake" class="text-gray-600 text-xs"
                        >Woda [l]</label
                    >
                    <input
                        id="water_intake"
                        type="number"
                        min="0"
                        max="20"
                        step="0.1"
                        v-model="form.water_intake"
                        class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                    />
                    <span
                        class="text-red-500 text-xs"
                        v-if="form.errors.water_intake"
                    >
                        {{ form.errors.water_intake }}
                    </span>
                </div>

                <div class="flex flex-col gap-1 w-full">
                    <label for="note" class="text-gray-600 text-xs"
                        >Notatka</label
                    >
                    <textarea
                        id="note"
                        v-model="form.note"
                        class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm resize-none h-28"
                    ></textarea>
                    <span class="text-red-500 text-xs" v-if="form.errors.note">
                        {{ form.errors.note }}
                    </span>
                </div>

                <PrimaryButton
                    :type="'submit'"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Dodaj wpis
                </PrimaryButton>
            </form>
        </div>

        <div
            class="flex flex-col gap-6 bg-white rounded-2xl shadow p-6 mb-6 break-inside-avoid"
        >
            <div class="flex flex-col gap-2">
                <h4 class="text-2xl font-normal">Historia wpisów</h4>
                <span class="text-sm text-gray-600">
                    Najnowsze wpisy są wyświetlane jako pierwsze.
                </span>
            </div>

            <div v-if="notes.length > 0" class="flex flex-col gap-4">
                <div
                    v-for="note in notes"
                    :key="note.id"
                    class="border border-slate-200 rounded-xl p-4 flex flex-col gap-3"
                >
                    <div class="flex justify-between gap-4">
                        <div>
                            <p class="text-lg font-normal">
                                {{ formatDate(note.date) }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Nastrój: {{ note.mood }}
                            </p>
                        </div>
                        <Link
                            method="DELETE"
                            :href="route('note.destroy', note)"
                            class="text-red-600 hover:text-red-700"
                            aria-label="Usuń wpis"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </Link>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                        <span>Energia: {{ note.energy_level }}/10</span>
                        <span>Stres: {{ note.stress_level }}/10</span>
                        <span>Sen: {{ note.sleep_hours }} h</span>
                        <span>Woda: {{ note.water_intake }} l</span>
                    </div>

                    <p v-if="note.note" class="text-sm text-gray-700">
                        {{ note.note }}
                    </p>
                </div>
            </div>
            <p v-else class="text-sm text-gray-600">
                Nie masz jeszcze wpisów w dzienniku.
            </p>
        </div>
    </div>
</template>
