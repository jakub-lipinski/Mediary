<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import MainBox from "@/Components/MainBox.vue";
import { Head } from "@inertiajs/vue3";

defineOptions({
    layout: MainLayout,
});

import { Link, usePage, useForm } from "@inertiajs/vue3";

import { computed, onBeforeUnmount, onMounted } from "vue";

const page = usePage();
const user = computed(() => page.props.user);
let weightChart = null;
let pressureChart = null;
let isMounted = false;

const props = defineProps({
    weights: Array,
    dates: Array,
    systolics: Array,
    diastolics: Array,
    blood_dates: Array,
    last_pressure: Object,
    files: Array,
});

var options = {
    series: [
        {
            name: "Waga",
            data: props.weights,
        },
    ],

    chart: {
        type: "area",
        height: 180,
        zoom: {
            enabled: false,
        },
        toolbar: {
            show: false,
        },
        tooltip: {
            enabled: false,
        },
        locales: [
            {
                name: "en",
                options: {
                    months: [
                        "Styczeń",
                        "Luty",
                        "Marzec",
                        "Kwiecień",
                        "Maj",
                        "Czerwiec",
                        "Lipiec",
                        "Sierpień",
                        "Wrzesień",
                        "Październik",
                        "Listopad",
                        "Grudzień",
                    ],
                    shortMonths: [
                        "Sty",
                        "Lut",
                        "Mar",
                        "Kwi",
                        "Maj",
                        "Cze",
                        "Lip",
                        "Sie",
                        "Wrz",
                        "Paź",
                        "Lis",
                        "Gru",
                    ],
                    days: [
                        "Niedziela",
                        "Poniedziałek",
                        "Wtorek",
                        "Środa",
                        "Czwartek",
                        "Piątek",
                        "Sobota",
                    ],
                    shortDays: ["Nd", "Pn", "Wt", "Śr", "Cz", "Pt", "Sb"],
                },
            },
        ],
    },
    dataLabels: {
        enabled: false,
    },
    stroke: {
        curve: "smooth",
        width: 3,
    },

    labels: props.dates,
    xaxis: {
        type: "category",
    },
    yaxis: {
        opposite: true,
        min: (min) => min - 5,
        max: (max) => max + 5,
    },
    legend: {
        horizontalAlign: "left",
    },
};

var optionsHeart = {
    colors: ["#be3434", "#e2bebe"],

    legend: {
        show: false,
    },
    series: [
        {
            name: "Ciśnienie skurczowe",
            data: props.systolics,
        },
        {
            name: "Ciśnienie rozkurczowe",
            data: props.diastolics,
        },
    ],
    chart: {
        height: 180,
        type: "line",
        zoom: {
            enabled: false,
        },
        toolbar: {
            show: false,
        },
    },
    dataLabels: {
        enabled: false,
    },
    stroke: {
        curve: "straight",
        width: 3,
    },
    labels: props.blood_dates,
    xaxis: {
        type: "category",
    },
    yaxis: {
        min: (min) => min - 10,
        max: (max) => max + 10,
    },
};

const form = useForm({
    file: null,
});

const upload = () => {
    form.post(route("file.store"), {
        onSuccess: () => {
            form.reset();
            document.querySelector("#add-file-modal").classList.add("hidden");
            document
                .querySelector("#add-file-background")
                .classList.add("hidden");
            const toastMagic = new ToastMagic();
            toastMagic.success(
                "Gotowe!",
                "Twój plik został zapisany i jest gotowy do analizy."
            );
        },
    });
};

const addFile = (e) => {
    form.file = e.target.files[0];
    if (form.file) {
        upload();
    }
};

const formatDate = (myDate) => {
    let customDate = new Date(myDate);
    return customDate.toLocaleDateString("pl-PL", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
};

onMounted(async () => {
    isMounted = true;
    try {
        const { default: ApexCharts } = await import("apexcharts");

        if (!isMounted) {
            return;
        }

        const chartElement = document.querySelector("#chart");
        if (chartElement) {
            weightChart = new ApexCharts(chartElement, options);
            weightChart.render();
        }

        const chartHeartElement = document.querySelector("#chart-heart");
        if (chartHeartElement) {
            pressureChart = new ApexCharts(chartHeartElement, optionsHeart);
            pressureChart.render();
        }
    } catch {
        weightChart = null;
        pressureChart = null;
    }

    const addFileBtn = document.querySelector("#add-file");
    const addFileBackground = document.querySelector("#add-file-background");
    const addFileModal = document.querySelector("#add-file-modal");
    const closeModalBtn = document.querySelector("#close-file-modal");

    addFileBtn?.addEventListener("click", () => {
        addFileModal.classList.remove("hidden");
        addFileBackground.classList.remove("hidden");
    });

    closeModalBtn?.addEventListener("click", () => {
        addFileModal.classList.add("hidden");
        addFileBackground.classList.add("hidden");
    });

    addFileBackground?.addEventListener("click", () => {
        if (!addFileModal.classList.contains("hidden")) {
            addFileModal.classList.add("hidden");
            addFileBackground.classList.add("hidden");
        }
    });
});

onBeforeUnmount(() => {
    isMounted = false;
    weightChart?.destroy();
    pressureChart?.destroy();
});
</script>

<template>
    <Head>
        <title>Pulpit</title>
    </Head>
    <!-- Start popup -->
    <div
        id="add-file-background"
        class="hidden fixed w-full h-full top-0 left-0 bg-black opacity-35 z-50"
    ></div>

    <div
        id="add-file-modal"
        class="hidden fixed bg-white rounded-2xl top-1/2 left-1/2 translate-x-[-50%] translate-y-[-50%] p-4 z-[51] w-[90%] max-w-[600px]"
    >
        <div class="flex justify-between items-center">
            <h2 for="cover-photo" class="text-xl font-normal">Prześlij plik</h2>
            <button id="close-file-modal">
                <i
                    class="fa-solid fa-xmark text-xl text-gray-300 duration-200 hover:text-gray-400"
                ></i>
            </button>
        </div>

        <form @submit.prevent="upload" class="mt-2 flex flex-col gap-2">
            <div
                class="text-center flex flex-col justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10"
            >
                <i class="fa-solid fa-file text-4xl text-gray-200"></i>
                <div
                    class="mt-4 flex text-sm leading-6 text-gray-600 justify-center"
                >
                    <label
                        for="file"
                        class="relative cursor-pointer text-center rounded-md bg-white font-semibold duration-200 text-blue-600 focus-within:outline-hidden focus-within:ring-2 focus-within:ring-blue-600 focus-within:ring-offset-2 hover:text-blue-500"
                    >
                        <span class="text-sm">Wybierz plik z urządzenia</span>
                        <input
                            id="file"
                            @change="addFile"
                            type="file"
                            class="sr-only"
                        />
                    </label>
                </div>
                <p class="text-xs leading-5 text-gray-600">
                    Pliki .pdf, .docx do 10MB
                </p>
                <span class="text-xs text-red-500" v-if="form.errors.file">{{
                    form.errors.file
                }}</span>
            </div>
            <!-- <label for="scan" class="text-sm text-gray-500"
                ><input
                    disabled
                    class="rounded-md h-4 w-4"
                    type="checkbox"
                    v-model="scan"
                    id="scan"
                />
                Wgrwyam skan
                <span class="text-blue-600 font-semibold">Wkrótce</span>
            </label> -->
        </form>
    </div>
    <!-- End popup -->

    <div class="flex flex-col mb-6">
        <h1 class="text-4xl font-normal">Dzień dobry, {{ user.name }}!</h1>
    </div>
    <div class="flex flex-col xl:flex-row lg:items-start gap-6">
        <div
            class="flex flex-col w-full xl:w-2/3 lg:flex-row items-start flex-wrap gap-6"
        >
            <div
                class="w-full h-fit lg:w-[calc(50%-12px)] flex bg-white shadow-xs rounded-2xl p-4 flex-col gap-2"
            >
                <div class="flex gap-2 items-center">
                    <div
                        class="flex justify-center items-center bg-blue-200 size-12 rounded-2xl"
                    >
                        <i
                            class="fa-solid fa-weight-scale text-blue-600 text-xl"
                        ></i>
                    </div>
                    <h4 class="text-2xl font-normal">Waga</h4>
                </div>
                <span v-if="user.proper_weight" class="text-sm text-gray-600"
                    >Prawidłowy zakres wagi na podstawie twoich danych to
                    {{ user.proper_weight }}</span
                >
                <span v-else class="text-sm text-gray-600"
                    >Uzupełnij
                    <Link class="text-blue-600" :href="route('profile.index')"
                        >podstawowe dane profilu</Link
                    >, aby obliczyć prawidłowy zakres wagi.</span
                >
                <div id="chart"></div>
                <div
                    class="flex justify-between flex-col gap-4 items-start lg:flex-row lg:items-end"
                >
                    <p v-if="user.weight" class="text-6xl font-light">
                        {{ user.weight
                        }}<span class="text-base font-ligh">kg</span>
                    </p>
                    <p v-else class="text-2xl font-light">
                        brak danych<span class="text-base font-ligh"></span>
                    </p>
                    <Link
                        :href="route('profile.index')"
                        class="text-white text-sm bg-blue-600 px-4 py-2 rounded-full flex justify-center items-center gap-2 duration-200 hover:bg-blue-700"
                    >
                        Dodaj pomiar <i class="fa-solid fa-plus"></i>
                    </Link>
                </div>
            </div>
            <div
                class="w-full lg:w-[calc(50%-12px)] h-fit flex bg-white shadow-xs rounded-2xl p-4 flex-col gap-2"
            >
                <div class="flex gap-2 items-center">
                    <div
                        class="flex justify-center items-center bg-red-200 size-12 rounded-2xl"
                    >
                        <i class="fa-solid fa-heart text-red-600 text-xl"></i>
                    </div>
                    <h4 class="text-2xl font-normal">Ciśnienie krwi</h4>
                </div>
                <span class="text-sm text-gray-600"
                    >Twoje ciśnienie powinno mieścić się w granicach 90–129 /
                    60–84 mmHg.</span
                >
                <div id="chart-heart"></div>
                <div
                    class="flex justify-between flex-col gap-4 items-start lg:flex-row lg:items-end"
                >
                    <p v-if="props.last_pressure" class="text-6xl font-light">
                        {{ props.last_pressure.systolic }}/{{
                            props.last_pressure.diastolic
                        }}<span class="text-base font-ligh">mmHg</span>
                    </p>
                    <p v-else class="text-2xl font-light">
                        brak danych<span class="text-base font-ligh"></span>
                    </p>
                    <Link
                        :href="route('profile.index')"
                        class="text-white text-sm bg-red-600 px-4 py-2 rounded-full flex justify-center items-center gap-2 duration-200 hover:bg-red-700"
                    >
                        Dodaj pomiar <i class="fa-solid fa-plus"></i>
                    </Link>
                </div>
            </div>
            <div
                class="w-full h-fit flex bg-white shadow-xs rounded-2xl p-5 flex-col gap-4 mt-0"
            >
                <div class="flex gap-3 items-center">
                    <div
                        class="flex justify-center items-center bg-emerald-100 size-11 rounded-xl"
                    >
                        <i
                            class="fa-solid fa-user-doctor text-emerald-600 text-lg"
                        ></i>
                    </div>
                    <div class="flex flex-col">
                        <h4 class="text-[19px] font-semibold text-slate-900 leading-6">Wirtualny specjalista</h4>
                        <span class="text-xs text-slate-500">AI — podsumowanie badań</span>
                    </div>
                    <span v-if="user.blood_recommendations" class="ml-auto hidden sm:inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-100 px-2.5 py-1 text-[11px] font-medium text-emerald-700">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span> Gotowe
                    </span>
                </div>

                <div v-if="user.blood_recommendations" class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                    <div
                        v-html="user.blood_recommendations"
                        class="prose prose-sm max-w-none prose-p:my-2.5 prose-p:leading-7 prose-p:text-slate-700 prose-strong:font-semibold prose-strong:text-slate-900 prose-b:font-semibold prose-b:text-slate-900 prose-ul:my-3 prose-ul:list-disc prose-ul:pl-5 prose-li:my-1 prose-li:text-slate-700 marker:text-emerald-400 prose-ul:marker:text-emerald-400"
                    ></div>
                    <div class="mt-4 flex gap-2.5 rounded-lg border border-amber-100 bg-amber-50 px-3 py-2.5">
                        <i class="fa-solid fa-shield-halved text-amber-500 text-xs mt-0.5"></i>
                        <p class="text-[11px] leading-4 text-amber-800">Wskazówki AI nie zastępują konsultacji lekarskiej. W razie wątpliwości skonsultuj wyniki z lekarzem.</p>
                    </div>
                </div>
                <div v-else class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-6 text-center">
                    <div class="mx-auto flex size-10 items-center justify-center rounded-full bg-white border border-slate-100 shadow-sm">
                        <i class="fa-solid fa-flask text-slate-400"></i>
                    </div>
                    <p class="mt-3 text-sm text-slate-600">
                        <Link class="font-medium text-blue-600 hover:text-blue-700" :href="route('blood.index')"
                            >Wypełnij wyniki</Link
                        >
                        swoich badań aby uzyskać rekomendację.
                    </p>
                    <p class="mt-1 text-xs text-slate-400">Kilka minut — analiza AI od razu na pulpicie.</p>
                </div>
            </div>
        </div>
        <div class="flex w-full xl:w-1/3">
            <div
                class="w-full flex bg-white shadow-xs rounded-2xl p-4 flex-col gap-2"
            >
                <div class="flex gap-2 items-center">
                    <div
                        class="flex justify-center items-center bg-blue-200 size-12 rounded-2xl"
                    >
                        <i
                            class="fa-solid fa-folder-open text-blue-600 text-xl"
                        ></i>
                    </div>
                    <h4 class="text-2xl font-normal">Twoje dokumenty</h4>
                    <button
                        id="add-file"
                        class="text-white text-sm bg-blue-600 size-9 ml-auto mr-0 rounded-full flex justify-center items-center"
                    >
                        <i class="fa-solid fa-plus text-base"></i>
                    </button>
                </div>
                <div class="flex flex-col gap-4 w-full">
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
                                v-if="file.type === 'pdf'"
                                class="fa-solid fa-file-pdf text-blue-600 text-xl"
                            ></i>
                            <i
                                v-else-if="file.type === 'doc' || file.type === 'docx'"
                                class="fa-solid fa-file-word text-blue-600 text-xl"
                            ></i>
                            <i
                                v-else
                                class="fa-solid fa-file text-blue-600 text-xl"
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
                            {{ formatDate(file.created_at) }}
                        </div>

                        <!-- Opcje -->
                        <!-- <div class="ml-2 shrink-0">
                            <button class="text-gray-600">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
