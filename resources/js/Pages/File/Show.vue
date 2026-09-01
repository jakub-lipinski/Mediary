<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import VuePdfEmbed from "vue-pdf-embed";
import { Link, Head } from "@inertiajs/vue3";

defineOptions({
    layout: MainLayout,
});

const props = defineProps({
    file: Object,
});

const back = () => {
    window.history.back();
};

const formatDate = (date) => {
    let newDate = new Date(date);
    return newDate.toLocaleDateString("pl-PL", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
};

const fileUrl = () => props.file.content_url;
</script>

<template>
    <Head>
        <title>Podgląd pliku</title>
        <meta
            name="description"
            content="Spersonalizowana analiza wyników badań i dopasowana dieta. Zadbaj o zdrowie z pomocą AI – bezpłatnie, bezpiecznie i w kilka minut."
        />
        <meta
            name="keywords"
            content="analiza wyników badań, dieta AI, zdrowie, asystent zdrowia, aplikacja zdrowotna, sztuczna inteligencja, dopasowana dieta, ciśnienie krwi, kontrola wagi, import wyników badań, raporty medyczne"
        />
    </Head>
    <div class="flex flex-wrap gap-6">
        <div
            class="flex flex-col gap-6 w-full lg:w-[calc(50%-12px)] bg-white rounded-2xl border-[1px] border-slate-200 p-6 h-fit"
        >
            <div class="flex flex-col gap-2">
                <div class="flex gap-2 items-center">
                    <div
                        class="flex justify-center items-center bg-blue-200 size-12 rounded-2xl"
                    >
                        <i
                            class="fa-solid fa-circle-info text-blue-600 text-xl"
                        ></i>
                    </div>
                    <h4 class="text-2xl font-normal">Informacje o pliku</h4>
                </div>
                <span class="text-sm text-gray-600"
                    >Podstawowe informacje oraz podsumowanie pliku.</span
                >
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="col-span-3 md:col-span-1 flex flex-col gap-1">
                    <span class="text-sm font-semibold text-blue-600"
                        >Nazwa pliku</span
                    >
                    <span class="text-sm">{{ file.filename }}</span>
                </div>

                <div class="col-span-3 md:col-span-1 flex flex-col gap-1">
                    <span class="text-sm font-semibold text-blue-600"
                        >Data przesłania</span
                    >
                    <span class="text-sm">{{
                        formatDate(file.created_at)
                    }}</span>
                </div>

                <div class="col-span-3 md:col-span-1 flex flex-col gap-1">
                    <span class="text-sm font-semibold text-blue-600"
                        >Rozmiar pliku</span
                    >
                    <span class="text-sm">{{ file.size }} MB</span>
                </div>

                <div class="col-span-3 flex flex-col gap-1">
                    <span class="text-sm font-semibold text-blue-600"
                        >Podsumowanie</span
                    >
                    <div
                        v-html="file.review"
                        class="text-sm flex flex-col gap-2"
                    ></div>
                </div>
                <div
                    class="w-full col-span-3 flex justify-between items-center"
                >
                    <PrimaryButton @click="back">Powrót</PrimaryButton>
                    <Link
                        method="delete"
                        :href="route('file.destroy', file.id)"
                    >
                        <i
                            class="fa-solid fa-trash text-red-600 duration-200 hover:text-red-700"
                        ></i>
                    </Link>
                </div>
            </div>
        </div>

        <div
            class="flex flex-col gap-6 w-full max-h-[700px] lg:w-[calc(50%-12px)] bg-white rounded-2xl border-[1px] border-slate-200 p-6 h-fit"
        >
            <div class="flex flex-col gap-2">
                <div class="flex gap-2 items-center">
                    <div
                        class="flex justify-center items-center bg-blue-200 size-12 rounded-2xl"
                    >
                        <i class="fa-solid fa-file text-blue-600 text-xl"></i>
                    </div>
                    <h4 class="text-2xl font-normal">Podgląd pliku</h4>
                </div>
                <span class="text-sm text-gray-600"
                    >Zobacz oryginalny plik.</span
                >
            </div>
            <div
                class="w-full h-[600px] overflow-y-hidden border-2 rounded-xl border-gray-200"
            >
                <VuePdfEmbed v-if="file.type === 'pdf'" :source="fileUrl()" />
                <div
                    v-else
                    class="h-full flex flex-col justify-center items-center gap-4 text-center p-6"
                >
                    <i class="fa-solid fa-file-word text-5xl text-blue-600"></i>
                    <p class="text-sm text-gray-600">
                        Podgląd tego formatu nie jest dostępny w aplikacji.
                    </p>
                    <a
                        :href="fileUrl()"
                        target="_blank"
                        rel="noopener"
                        class="text-white text-sm bg-blue-600 px-4 py-2 rounded-full duration-200 hover:bg-blue-700"
                    >
                        Otwórz plik
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
