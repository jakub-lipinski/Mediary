<script setup>
const props = defineProps({
    width: String,
});
</script>

<template>
    <div
        class="w-full h-fit flex bg-white shadow-xs rounded-2xl p-4 flex-col gap-2"
        :class="{
            'lg:w-[calc(50%-12px)]': props.width === 'half',
            'xl:w-1/3': props.width === 'third',
        }"
    >
        <div class="flex gap-2 items-center">
            <div
                class="flex justify-center items-center bg-blue-200 size-12 rounded-2xl"
            >
                <i class="fa-solid fa-weight-scale text-blue-600 text-xl"></i>
            </div>
            <h4 class="text-2xl font-normal">
                <slot name="title"></slot>
            </h4>
        </div>
    </div>
</template>
