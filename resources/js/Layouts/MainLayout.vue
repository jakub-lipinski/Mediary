<script setup>
import { Link } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";

const page = usePage();

const user = computed(() => page.props.auth.user);

const getAvatar = () => {
    return (
        "https://ui-avatars.com/api/?name=" +
        user.value.name +
        "&background=2563EB&color=fff&length=1"
    );
};

onMounted(() => {
    const userMenuBtn = document.querySelector(".user-menu-button");
    const userMenu = document.querySelector(".user-menu");
    const MobileMenuOpen = document.querySelector(".mobile-menu-open");
    const MobileMenuClose = document.querySelector(".close-sidebar");
    const mobileBackdrop = document.querySelector(".mobile-backdrop");
    const mobileMenu = document.querySelector(".mobile-menu");
    const mobileLinks = [...document.querySelectorAll(".mobile-link")];

    const closeMobileMenu = () => {
        mobileBackdrop.classList.remove("opacity-100");
        mobileBackdrop.classList.add("opacity-0");
        mobileBackdrop.classList.remove("h-full");
        mobileBackdrop.classList.add("h-0");
        mobileMenu.classList.add("-translate-x-full");
    };

    MobileMenuOpen?.addEventListener("click", () => {
        if (mobileBackdrop.classList.contains("opacity-0")) {
            mobileBackdrop.classList.remove("opacity-0");
            mobileBackdrop.classList.add("opacity-100");
            mobileBackdrop.classList.remove("h-0");
            mobileBackdrop.classList.add("h-full");

            mobileMenu.classList.remove("-translate-x-full");
        } else {
            mobileBackdrop.classList.remove("opacity-100");
            mobileBackdrop.classList.add("opacity-0");
            mobileBackdrop.classList.remove("h-full");
            mobileBackdrop.classList.add("h-0");

            mobileMenu.classList.add("-translate-x-full");
        }
    });

    MobileMenuClose?.addEventListener("click", closeMobileMenu);

    mobileLinks.forEach((link) => {
        link.addEventListener("click", closeMobileMenu);
    });

    userMenuBtn?.addEventListener("click", () => {
        userMenu.classList.toggle("opacity-0");
        userMenu.classList.toggle("invisible");
        userMenu.classList.toggle("scale-95");
    });
});
</script>

<template>
    <div class="w-full">
        <div class="relative z-50" role="dialog" aria-modal="true">
            <div
                class="mobile-backdrop fixed inset-0 opacity-0 duration-300 ease-linear transition-opacity h-0 bg-gray-900/50"
            ></div>

            <div
                class="mobile-menu duration-300 -translate-x-full ease-in-out fixed inset-0 flex w-fit"
            >
                <div
                    class="relative mr-16 flex w-full max-w-xs flex-1 border-r border-gray-200"
                >
                    <div
                        class="absolute left-full top-0 flex w-16 justify-center pt-5"
                    >
                        <button
                            type="button"
                            class="close-sidebar -m-2.5 p-2.5"
                        >
                            <span class="sr-only">Close sidebar</span>
                            <svg
                                class="h-6 w-6 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                    <div
                        class="flex flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4"
                    >
                        <div class="flex h-16 shrink-0 items-center">
                            <img class="h-8 w-auto" src="/img/logo_1.png" />
                        </div>

                        <nav class="flex flex-1 flex-col">
                            <ul
                                role="list"
                                class="flex flex-1 flex-col gap-y-7"
                            >
                                <li>
                                    <ul role="list" class="-mx-2 space-y-1">
                                        <li>
                                            <Link
                                                :href="route('dashboard')"
                                                :class="{
                                                    'bg-blue-600 text-blue-600 shadow-2xl':
                                                        route().current(
                                                            'dashboard'
                                                        ),
                                                    'text-gray-600 hover:text-blue-600 hover:bg-gray-50':
                                                        !route().current(
                                                            'dashboard'
                                                        ),
                                                }"
                                                class="mobile-link flex justify-center items-center gap-x-3 rounded-full p-3 text-sm font-semibold transition-colors duration-300 size-12"
                                            >
                                                <i
                                                    :class="{
                                                        'text-white':
                                                            route().current(
                                                                'dashboard'
                                                            ),
                                                    }"
                                                    class="fa-solid fa-house text-lg"
                                                ></i>
                                            </Link>
                                        </li>
                                        <li>
                                            <Link
                                                :href="route('profile.index')"
                                                :class="{
                                                    'bg-blue-600 text-blue-600 shadow-2xl':
                                                        route().current(
                                                            'profile.*'
                                                        ),
                                                    'text-gray-600 hover:text-blue-600 hover:bg-gray-50':
                                                        !route().current(
                                                            'profile.*'
                                                        ),
                                                }"
                                                class="mobile-link flex justify-center items-center gap-x-3 rounded-full p-3 text-sm font-semibold transition-colors duration-300 size-12"
                                            >
                                                <i
                                                    :class="{
                                                        'text-white':
                                                            route().current(
                                                                'profile.*'
                                                            ),
                                                    }"
                                                    class="fa-solid fa-user text-lg"
                                                ></i>
                                            </Link>
                                        </li>

                                        <li>
                                            <Link
                                                :href="route('blood.index')"
                                                :class="{
                                                    'bg-blue-600 text-blue-600 shadow-2xl':
                                                        route().current(
                                                            'blood.*'
                                                        ),
                                                    'text-gray-600 hover:text-blue-600 hover:bg-gray-50':
                                                        !route().current(
                                                            'blood.*'
                                                        ),
                                                }"
                                                class="mobile-link flex justify-center items-center gap-x-3 rounded-full p-3 text-sm font-semibold transition-colors duration-300 size-12"
                                            >
                                                <i
                                                    :class="{
                                                        'text-white':
                                                            route().current(
                                                                'blood.*'
                                                            ),
                                                    }"
                                                    class="fa-solid fa-flask-vial text-lg"
                                                ></i>
                                            </Link>
                                        </li>

                                        <li>
                                            <Link
                                                :href="route('diet.index')"
                                                :class="{
                                                    'bg-blue-600 text-blue-600 shadow-2xl':
                                                        route().current(
                                                            'diet.*'
                                                        ),
                                                    'text-gray-600 hover:text-blue-600 hover:bg-gray-50':
                                                        !route().current(
                                                            'diet.*'
                                                        ),
                                                }"
                                                class="mobile-link flex justify-center items-center gap-x-3 rounded-full p-3 text-sm font-semibold transition-colors duration-300 size-12"
                                            >
                                                <i
                                                    :class="{
                                                        'text-white':
                                                            route().current(
                                                                'diet.*'
                                                            ),
                                                    }"
                                                    class="fa-solid fa-utensils text-lg"
                                                ></i>
                                            </Link>
                                        </li>

                                        <li>
                                            <Link
                                                :href="route('note.index')"
                                                :class="{
                                                    'bg-blue-600 text-blue-600 shadow-2xl':
                                                        route().current(
                                                            'note.*'
                                                        ),
                                                    'text-gray-600 hover:text-blue-600 hover:bg-gray-50':
                                                        !route().current(
                                                            'note.*'
                                                        ),
                                                }"
                                                class="mobile-link flex justify-center items-center gap-x-3 rounded-full p-3 text-sm font-semibold transition-colors duration-300 size-12"
                                            >
                                                <i
                                                    :class="{
                                                        'text-white':
                                                            route().current(
                                                                'note.*'
                                                            ),
                                                    }"
                                                    class="fa-solid fa-book-medical text-lg"
                                                ></i>
                                            </Link>
                                        </li>
                                    </ul>
                                </li>
                            </ul>

                            <ul
                                role="list"
                                class="flex flex-1 flex-col gap-y-7 mt-auto mb-0 justify-end items-end"
                            >
                                <li>
                                    <ul role="list" class="-mx-2 space-y-1">
                                        <li>
                                            <Link
                                                method="POST"
                                                :href="route('logout')"
                                                class="mobile-link text-gray-600 hover:text-blue-600 hover:bg-gray-50 flex justify-center items-center gap-x-3 rounded-full p-3 text-sm font-semibold transition-colors duration-300 size-12"
                                            >
                                                <i
                                                    class="fa-solid fa-arrow-right-from-bracket text-xl"
                                                ></i>
                                            </Link>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Destop sidebar start -->
        <div
            class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-24 lg:flex-col justify-center items-center border-r border-gray-200"
        >
            <div
                class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4 items-center"
            >
                <div class="flex h-16 shrink-0 items-center">
                    <img class="w-12 h-11" src="/img/logo_1.png" />
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                <li>
                                    <Link
                                        :href="route('dashboard')"
                                        :class="{
                                            'bg-blue-600 text-blue-600 shadow-2xl':
                                                route().current('dashboard'),
                                            'text-gray-600 hover:text-blue-600 hover:bg-gray-50':
                                                !route().current('dashboard'),
                                        }"
                                        class="flex justify-center items-center gap-x-3 rounded-full p-3 text-sm font-semibold transition-colors duration-300 size-12"
                                    >
                                        <i
                                            :class="{
                                                'text-white':
                                                    route().current(
                                                        'dashboard'
                                                    ),
                                            }"
                                            class="fa-solid fa-house text-lg"
                                        ></i>
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        :href="route('profile.index')"
                                        :class="{
                                            'bg-blue-600 text-blue-600 shadow-2xl':
                                                route().current('profile.*'),
                                            'text-gray-600 hover:text-blue-600 hover:bg-gray-50':
                                                !route().current('profile.*'),
                                        }"
                                        class="flex justify-center items-center gap-x-3 rounded-full p-3 text-sm font-semibold transition-colors duration-300 size-12"
                                    >
                                        <i
                                            :class="{
                                                'text-white':
                                                    route().current(
                                                        'profile.*'
                                                    ),
                                            }"
                                            class="fa-solid fa-user text-lg"
                                        ></i>
                                    </Link>
                                </li>

                                <li>
                                    <Link
                                        :href="route('blood.index')"
                                        :class="{
                                            'bg-blue-600 text-blue-600 shadow-2xl':
                                                route().current('blood.*'),
                                            'text-gray-600 hover:text-blue-600 hover:bg-gray-50':
                                                !route().current('blood.*'),
                                        }"
                                        class="flex justify-center items-center gap-x-3 rounded-full p-3 text-sm font-semibold transition-colors duration-300 size-12"
                                    >
                                        <i
                                            :class="{
                                                'text-white':
                                                    route().current('blood.*'),
                                            }"
                                            class="fa-solid fa-flask-vial text-lg"
                                        ></i>
                                    </Link>
                                </li>

                                <li>
                                    <Link
                                        :href="route('diet.index')"
                                        :class="{
                                            'bg-blue-600 text-blue-600 shadow-2xl':
                                                route().current('diet.*'),
                                            'text-gray-600 hover:text-blue-600 hover:bg-gray-50':
                                                !route().current('diet.*'),
                                        }"
                                        class="flex justify-center items-center gap-x-3 rounded-full p-3 text-sm font-semibold transition-colors duration-300 size-12"
                                    >
                                        <i
                                            :class="{
                                                'text-white':
                                                    route().current('diet.*'),
                                            }"
                                            class="fa-solid fa-utensils text-lg"
                                        ></i>
                                    </Link>
                                </li>

                                <li>
                                    <Link
                                        :href="route('note.index')"
                                        :class="{
                                            'bg-blue-600 text-blue-600 shadow-2xl':
                                                route().current('note.*'),
                                            'text-gray-600 hover:text-blue-600 hover:bg-gray-50':
                                                !route().current('note.*'),
                                        }"
                                        class="flex justify-center items-center gap-x-3 rounded-full p-3 text-sm font-semibold transition-colors duration-300 size-12"
                                    >
                                        <i
                                            :class="{
                                                'text-white':
                                                    route().current('note.*'),
                                            }"
                                            class="fa-solid fa-book-medical text-lg"
                                        ></i>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>

                    <ul
                        role="list"
                        class="flex flex-1 flex-col gap-y-7 mt-auto mb-0 justify-end items-end"
                    >
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                <li>
                                    <Link
                                        method="POST"
                                        :href="route('logout')"
                                        class="text-gray-600 hover:text-blue-600 hover:bg-gray-50 flex justify-center items-center gap-x-3 rounded-full p-3 text-sm font-semibold transition-colors duration-300 size-12"
                                    >
                                        <i
                                            class="fa-solid fa-arrow-right-from-bracket text-xl"
                                        ></i>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <!-- Desktop sidebar end -->

        <div class="h-min-screen lg:pl-24">
            <main class="min-h-screen w-full bg-[#F9FAFC]">
                <div
                    class="sticky top-0 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 sm:gap-x-6 sm:px-6 lg:px-8 z-50"
                >
                    <button
                        type="button"
                        class="mobile-menu-open -m-2.5 p-2.5 text-gray-700 lg:hidden"
                    >
                        <span class="sr-only">Open sidebar</span>
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                            />
                        </svg>
                    </button>

                    <!-- Separator -->
                    <div
                        class="h-6 w-px bg-gray-200 lg:hidden"
                        aria-hidden="true"
                    ></div>

                    <div
                        class="flex flex-1 items-center justify-end gap-x-4 self-stretch lg:gap-x-6"
                    >
                        <div class="flex items-center gap-x-4 lg:gap-x-6">
                            <!-- Profile dropdown -->
                            <div class="relative">
                                <button
                                    type="button"
                                    class="user-menu-button -m-1.5 flex items-center p-1.5 duartion-200 hover:bg-gray-50 rounded-md"
                                    id="user-menu-button"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                >
                                    <span class="sr-only">Open user menu</span>
                                    <img
                                        class="h-7 w-7 rounded-full bg-gray-50"
                                        :src="getAvatar(user.name)"
                                        alt=""
                                    />
                                    <span
                                        class="ml-2 text-gray-600"
                                        v-if="user.name"
                                        >{{ user.name }}</span
                                    >

                                    <span
                                        class="hidden lg:flex lg:items-center"
                                    >
                                        <svg
                                            class="ml-2 h-5 w-5 text-gray-400"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </button>

                                <div
                                    class="user-menu absolute duaration-100 opacity-0 invisible scale-95 right-0 z-50 mt-2.5 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-hidden"
                                    role="menu"
                                    aria-orientation="vertical"
                                    aria-labelledby="user-menu-button"
                                    tabindex="-1"
                                >
                                    <!-- Active: "bg-gray-50", Not Active: "" -->
                                    <Link
                                        :href="route('profile.edit')"
                                        class="block px-3 py-1 text-sm leading-6 text-gray-900 hover:bg-gray-50"
                                        role="menuitem"
                                        tabindex="-1"
                                        id="user-menu-item-0"
                                        >Edytuj konto</Link
                                    >
                                    <Link
                                        method="POST"
                                        :href="route('logout')"
                                        class="block px-3 py-1 text-sm leading-6 text-gray-900 hover:bg-gray-50"
                                        role="menuitem"
                                        tabindex="-1"
                                        id="user-menu-item-1"
                                        >Wyloguj się</Link
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <slot></slot>
                    <div class="h-4 w-full bg-red"></div>
                </div>
            </main>
        </div>
    </div>
</template>
