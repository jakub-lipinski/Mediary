<script setup>
import { Link } from "@inertiajs/vue3";
import { onMounted, computed } from "vue";
import Swiper from "swiper";
import { Pagination } from "swiper/modules";
import { Head, usePage } from "@inertiajs/vue3";
import "swiper/css";
import "swiper/css/pagination";

const page = usePage();
const url = computed(() => page.props.app_url);

onMounted(() => {
    new Swiper(".swiper", {
        modules: [Pagination],
        direction: "horizontal",
        loop: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },

        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
        },
    });

    const mobileNav = document.querySelector(".mobile-nav");
    const dekstopNav = document.querySelector(".desktop-nav");
    let scrollValue = 0;

    window.addEventListener("scroll", () => {
        scrollValue = window.scrollY;
        if (scrollValue > 0) {
            dekstopNav.classList.add("desktop-nav--active");
            mobileNav.classList.add("mobile-nav--active");
        } else {
            mobileNav.classList.remove("mobile-nav--active");
            dekstopNav.classList.remove("desktop-nav--active");
        }
    });

    const hamburger = document.querySelector(".mobile-nav__hamburger");
    const sidebar = document.querySelector(".mobile-nav__sidebar");
    const closeBtn = document.querySelector(".mobile-nav__close");
    const mobileOptions = [...document.querySelectorAll(".mobile-nav__option")];

    hamburger.addEventListener("click", () => {
        sidebar.classList.add("mobile-nav__sidebar--active");
        closeBtn.classList.add("mobile-nav__close--active");
        hamburger.classList.remove("mobile-nav__hamburger--active");
    });

    closeBtn.addEventListener("click", () => {
        sidebar.classList.remove("mobile-nav__sidebar--active");
        closeBtn.classList.remove("mobile-nav__close--active");
        hamburger.classList.add("mobile-nav__hamburger--active");
    });

    mobileOptions.forEach((option) => {
        option.addEventListener("click", () => {
            sidebar.classList.remove("mobile-nav__sidebar--active");
            closeBtn.classList.remove("mobile-nav__close--active");
            hamburger.classList.add("mobile-nav__hamburger--active");
        });
    });
});
</script>

<template>
    <Head>
        <title>
            Mediary: Zadbaj o zdrowie i lepsze samopoczucie z pomocą AI
        </title>
        <meta
            name="description"
            content="Aplikacja Mediary analizuje Twoje wyniki badań i parametry zdrowotne, tworząc spersonalizowaną dietę, raporty i zalecenia wspierane przez AI."
        />
        <meta
            name="keywords"
            content="mediary, zdrowie, dieta, analiza wyników badań, aplikacja zdrowotna, sztuczna inteligencja, AI, monitorowanie ciśnienia, śledzenie wagi, raporty zdrowotne, spersonalizowana dieta"
        />
        <meta
            property="og:title"
            content="Mediary: Zadbaj o zdrowie i lepsze samopoczucie z pomocą AI"
        />
        <meta property="og:url" :content="url" />
        <meta property="og:image" :content="`${url}/img/og.png`" />
    </Head>
    <div class="website-container">
        <nav class="desktop-nav">
            <div class="desktop-nav__container">
                <div class="desktop-nav__logo-container">
                    <img
                        src="/img/logo_1.png"
                        alt=""
                        class="desktop-nav__logo"
                    />
                </div>

                <ul class="desktop-nav__menu">
                    <li class="desktop-nav__option">
                        <a href="#start" class="desktop-nav__link">Start</a>
                    </li>
                    <li class="desktop-nav__option">
                        <a href="#about" class="desktop-nav__link"
                            >O aplikacji</a
                        >
                    </li>
                    <li class="desktop-nav__option">
                        <a href="#faq" class="desktop-nav__link">FAQ</a>
                    </li>
                    <li class="desktop-nav__option">
                        <a href="#testimonials" class="desktop-nav__link"
                            >Opinie</a
                        >
                    </li>
                    <li class="desktop-nav__option">
                        <a
                            href="mailto:kontakt@mediary.pl"
                            class="desktop-nav__link"
                            >Kontakt</a
                        >
                    </li>
                </ul>
                <div class="desktop-nav__buttons">
                    <Link
                        v-if="$page.props.user"
                        :href="route('login')"
                        class="desktop-nav__button"
                        >Panel użytkownika</Link
                    >
                    <Link
                        v-if="!$page.props.user"
                        :href="route('login')"
                        class="desktop-nav__button desktop-nav__button--empty"
                        >Zaloguj się</Link
                    >
                    <Link
                        v-if="!$page.props.user"
                        :href="route('register')"
                        class="desktop-nav__button"
                        >Zarejestruj się</Link
                    >
                </div>
            </div>
        </nav>

        <nav class="mobile-nav">
            <div class="mobile-nav__container">
                <img src="/img/logo_1.png" alt="" class="mobile-nav__logo" />
                <button class="mobile-nav__hamburger-button">
                    <i
                        class="fa-solid fa-bars mobile-nav__hamburger mobile-nav__hamburger--active"
                    ></i>
                    <i class="fa-solid fa-xmark mobile-nav__close"></i>
                </button>
            </div>
            <div class="mobile-nav__sidebar">
                <ul class="mobile-nav__menu">
                    <img
                        src="/img/logo_1.png"
                        alt=""
                        class="mobile-nav__logo"
                    />
                    <li class="mobile-nav__option">
                        <a href="#start" class="mobile-nav__link">Start</a>
                    </li>
                    <li class="mobile-nav__option">
                        <a href="#about" class="mobile-nav__link"
                            >O aplikacji</a
                        >
                    </li>
                    <li class="mobile-nav__option">
                        <a href="#faq" class="mobile-nav__link">FAQ</a>
                    </li>
                    <li class="mobile-nav__option">
                        <a href="#testimonials" class="mobile-nav__link"
                            >Opinie</a
                        >
                    </li>
                    <li class="mobile-nav__option">
                        <a
                            href="mailto:kontakt@mediary.pl"
                            class="mobile-nav__link"
                            >Kontakt</a
                        >
                    </li>
                    <li class="mobile-nav__option">
                        <Link
                            :href="route('login')"
                            class="mobile-nav__link mobile-nav__link--empty"
                            >Zaloguj się</Link
                        >
                    </li>
                    <li class="mobile-nav__option">
                        <Link
                            :href="route('register')"
                            class="mobile-nav__link mobile-nav__link--cta"
                            >Zarejestruj się</Link
                        >
                    </li>
                </ul>
            </div>
        </nav>

        <section id="start" class="hero">
            <!-- <div class="hero__circle"></div> -->

            <div class="hero__container">
                <div class="hero__text">
                    <h1 class="hero__title">
                        Zadbaj o zdrowie i lepsze samopoczucie z pomocą AI
                    </h1>
                    <p class="hero__description">
                        Aplikacja, która analizuje Twoje wyniki badań i
                        parametry zdrowotne, aby tworzyć dopasowane diety
                        wspierające zdrowie i cele żywieniowe.
                    </p>
                    <div class="hero__buttons">
                        <Link :href="route('register')" class="hero__button"
                            >Wypróbuj za darmo</Link
                        >
                        <a
                            href="#about"
                            class="hero__button hero__button--empty"
                            >Dowiedz się więcej
                            <i class="hero__icon fa-solid fa-arrow-right"></i
                        ></a>
                    </div>
                </div>
                <img src="/img/hero.png" alt="" class="hero__image" />
            </div>
            <div class="hero__features">
                <div class="hero__features-container">
                    <div class="hero__feature">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 -960 960 960"
                            class="hero__feature-icon"
                        >
                            <path
                                d="M80-600v-120q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v120h-80v-120H160v120H80Zm80 440q-33 0-56.5-23.5T80-240v-120h80v120h640v-120h80v120q0 33-23.5 56.5T800-160H160Zm240-120q11 0 21-5.5t15-16.5l124-248 44 88q5 11 15 16.5t21 5.5h240v-80H665l-69-138q-5-11-15-15.5t-21-4.5q-11 0-21 4.5T524-658L400-410l-44-88q-5-11-15-16.5t-21-5.5H80v80h215l69 138q5 11 15 16.5t21 5.5Zm80-200Z"
                            />
                        </svg>
                        <h4 class="hero__feature-name">Analiza zdrowia</h4>
                        <p class="hero__feature-description">
                            Wprowadź wyniki badań lub parametry organizmu, a
                            nasza aplikacja przeanalizuje je z pomocą AI i
                            podpowie, co warto poprawić.
                        </p>
                    </div>
                    <div class="hero__feature">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 -960 960 960"
                            class="hero__feature-icon"
                        >
                            <path
                                d="M240-80v-366q-54-14-87-57t-33-97v-280h80v240h40v-240h80v240h40v-240h80v280q0 54-33 97t-87 57v366h-80Zm400 0v-381q-54-18-87-75.5T520-667q0-89 47-151t113-62q66 0 113 62.5T840-666q0 73-33 130t-87 75v381h-80Z"
                            />
                        </svg>
                        <h4 class="hero__feature-name">Dopasowana dieta</h4>
                        <p class="hero__feature-description">
                            Twórz diety dostosowane do Twoich wyników, stylu
                            życia i celów zdrowotnych – bez liczenia kalorii i
                            zgadywania.
                        </p>
                    </div>
                    <div class="hero__feature">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 -960 960 960"
                            class="hero__feature-icon"
                        >
                            <path
                                d="M440-200h80v-167l64 64 56-57-160-160-160 160 57 56 63-63v167ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z"
                            />
                        </svg>
                        <h4 class="hero__feature-name">Import wyników</h4>
                        <p class="hero__feature-description">
                            Prześlij wyniki badań z laboratorium – aplikacja
                            automatycznie rozpozna dane i dopasuje zalecenia.
                        </p>
                    </div>
                    <div class="hero__feature">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 -960 960 960"
                            class="hero__feature-icon"
                        >
                            <path
                                d="M280-280h80v-200h-80v200Zm320 0h80v-400h-80v400Zm-160 0h80v-120h-80v120Zm0-200h80v-80h-80v80ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm0-560v560-560Z"
                            />
                        </svg>
                        <h4 class="hero__feature-name">Czytelne raporty</h4>
                        <p class="hero__feature-description">
                            Otrzymuj przejrzyste raporty i rekomendacje – w
                            zrozumiałej formie, bez medycznego żargonu.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="section bento">
            <div class="section__container section__container--first">
                <div class="section__heading">
                    <h6 class="section__subtitle">Co potrafi aplikacja?</h6>
                    <h2 class="section__title">
                        Wszystko, czego potrzebujesz, by lepiej zadbać o zdrowie
                    </h2>
                </div>
                <div class="bento__container">
                    <div class="bento__column">
                        <div class="bento__tile bento__tile--tall">
                            <h3 class="bento__title">Stwórz idealną dietę</h3>
                            <p class="bento__description">
                                Dopasuj kalorie, posiłki i preferencje.
                                Otrzymasz dietę stworzoną przez AI na podstawie
                                Twoich potrzeb.
                            </p>
                            <div class="bento__image-wrapper">
                                <img
                                    src="/img/bento_1.png"
                                    class="bento__image bento__image--tall"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="bento__column">
                        <div class="bento__tile">
                            <h3 class="bento__title">
                                Monitoruj ciśnienie krwi
                            </h3>
                            <p class="bento__description">
                                Zapisuj ciśnienie krwi, a asystent AI analizuje
                                historię i generuje opinię przy każdym pomiarze.
                            </p>
                            <img
                                src="/img/bento_2.png"
                                class="bento__image bento__image--border"
                            />
                        </div>
                        <div class="bento__tile">
                            <h3 class="bento__title">Śledź swoją wagę</h3>
                            <p class="bento__description">
                                Dodawaj pomiary wagi, a AI uwzględni zmiany w
                                analizie wyników i dopasuje zalecenia oraz dietę
                                do aktualnej sytuacji.
                            </p>
                            <img
                                src="/img/bento_3.png"
                                class="bento__image bento__image--border"
                            />
                        </div>
                    </div>
                    <div class="bento__column">
                        <div class="bento__tile bento__tile--tall">
                            <h3 class="bento__title">Oceń wyniki badań</h3>
                            <p class="bento__description">
                                Wgraj plik z laboratorium, a AI przeanalizuje
                                kluczowe wskaźniki i wyjaśni, co mogą oznaczać.
                            </p>
                            <div class="bento__image-wrapper">
                                <img
                                    src="/img/bento_4.png"
                                    class="bento__image bento__image--tall"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="faq" class="section faq">
            <div class="section__container">
                <div class="section__heading section__heading--left">
                    <h6 class="section__subtitle">FAQ</h6>
                    <h2 class="section__title">Najczęściej zadawane pytania</h2>
                    <p class="section__description">
                        Masz pytania dotyczące działania aplikacji, prywatności
                        lub obsługi danych? Zebraliśmy odpowiedzi na najczęściej
                        pojawiające się wątpliwości, aby ułatwić Ci start i
                        pokazać, jak w praktyce działa nasz system analizy
                        zdrowia wspierany przez AI.
                    </p>
                </div>
                <div class="faq__container">
                    <div class="faq__item">
                        <span class="faq__question">Jak działa aplikacja?</span>
                        <p class="faq__answer">
                            Analizuje Twoje dane zdrowotne i pomaga lepiej
                            zrozumieć wyniki oraz stan organizmu.
                        </p>
                    </div>
                    <div class="faq__item">
                        <span class="faq__question"
                            >Jakie pliki mogę przesłać?</span
                        >
                        <p class="faq__answer">
                            Na razie obsługiwane są tylko pliki PDF z wynikami
                            badań.
                        </p>
                    </div>
                    <div class="faq__item">
                        <span class="faq__question"
                            >Czy otrzymam konkretne zalecenia?</span
                        >
                        <p class="faq__answer">
                            Tak – AI generuje opinie na podstawie Twoich danych
                            i historii pomiarów.
                        </p>
                    </div>
                    <div class="faq__item">
                        <span class="faq__question"
                            >Czy mogę aktualizować swoje pomiary?</span
                        >
                        <p class="faq__answer">
                            Tak – możesz dodawać wagę, ciśnienie i inne dane, a
                            aplikacja uwzględnia je w analizie.
                        </p>
                    </div>
                    <div class="faq__item">
                        <span class="faq__question"
                            >Czy aplikacja działa na telefonie?</span
                        >
                        <p class="faq__answer">
                            Tak – aplikacja jest responsywna i działa zarówno na
                            komputerze, jak i smartfonie.
                        </p>
                    </div>
                    <div class="faq__item">
                        <span class="faq__question"
                            >Czy mogę w pełni ufać zaleceniom?
                        </span>
                        <p class="faq__answer">
                            To sugestie wygenerowane przez AI na podstawie
                            danych. Mogą nie być w pełni trafne – zawsze należy
                            skonsultować je z lekarzem.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section id="testimonials" class="section testimonials">
            <div class="section__container">
                <div class="section__heading">
                    <h6 class="section__subtitle">
                        Co mówią nasi użytkownicy?
                    </h6>
                    <h2 class="section__title">
                        Zobacz, jak aplikacja wspiera zdrowie na co dzień
                    </h2>
                </div>
                <div class="swiper testimonials__swiper">
                    <!-- Additional required wrapper -->
                    <div class="swiper-wrapper testimonials__swiper-wrapper">
                        <!-- Slides -->

                        <div class="swiper-slide">
                            <div class="testimonials__item">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 -960 960 960"
                                    class="testimonials__icon"
                                >
                                    <path
                                        d="m228-240 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T458-480L320-240h-92Zm360 0 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T818-480L680-240h-92ZM320-500q25 0 42.5-17.5T380-560q0-25-17.5-42.5T320-620q-25 0-42.5 17.5T260-560q0 25 17.5 42.5T320-500Zm360 0q25 0 42.5-17.5T740-560q0-25-17.5-42.5T680-620q-25 0-42.5 17.5T620-560q0 25 17.5 42.5T680-500Zm0-60Zm-360 0Z"
                                    />
                                </svg>
                                <p class="testimonials__content">
                                    W końcu znalazłam aplikację, która nie tylko
                                    liczy kalorie, ale rozumie kontekst
                                    zdrowotny. Świetnie dopasowuje zalecenia pod
                                    moje wyniki badań.
                                </p>
                                <div class="testimonials__line"></div>
                                <div class="testimonials__bottom">
                                    <p class="testimonials__name">
                                        Marta, trenerka personalna
                                    </p>
                                    <div class="testimonials__rating-container">
                                        <div class="testimonials__stars">
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                        </div>
                                        <span class="testimonials__rating"
                                            >Ocena
                                            <span
                                                class="testimonials__rating testimonials__rating--bold"
                                                >5.0</span
                                            >
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="testimonials__item">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 -960 960 960"
                                    class="testimonials__icon"
                                >
                                    <path
                                        d="m228-240 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T458-480L320-240h-92Zm360 0 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T818-480L680-240h-92ZM320-500q25 0 42.5-17.5T380-560q0-25-17.5-42.5T320-620q-25 0-42.5 17.5T260-560q0 25 17.5 42.5T320-500Zm360 0q25 0 42.5-17.5T740-560q0-25-17.5-42.5T680-620q-25 0-42.5 17.5T620-560q0 25 17.5 42.5T680-500Zm0-60Zm-360 0Z"
                                    />
                                </svg>
                                <p class="testimonials__content">
                                    Zaczęło się od wysokiego ciśnienia. Teraz
                                    regularnie dodaję pomiary, a AI podpowiada,
                                    czy coś się zmienia. Czuję się spokojniejszy
                                    i bardziej świadomy.
                                </p>
                                <div class="testimonials__line"></div>
                                <div class="testimonials__bottom">
                                    <p class="testimonials__name">
                                        Paweł, biegacz amator
                                    </p>
                                    <div class="testimonials__rating-container">
                                        <div class="testimonials__stars">
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                        </div>
                                        <span class="testimonials__rating"
                                            >Ocena
                                            <span
                                                class="testimonials__rating testimonials__rating--bold"
                                                >5.0</span
                                            >
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="testimonials__item">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 -960 960 960"
                                    class="testimonials__icon"
                                >
                                    <path
                                        d="m228-240 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T458-480L320-240h-92Zm360 0 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T818-480L680-240h-92ZM320-500q25 0 42.5-17.5T380-560q0-25-17.5-42.5T320-620q-25 0-42.5 17.5T260-560q0 25 17.5 42.5T320-500Zm360 0q25 0 42.5-17.5T740-560q0-25-17.5-42.5T680-620q-25 0-42.5 17.5T620-560q0 25 17.5 42.5T680-500Zm0-60Zm-360 0Z"
                                    />
                                </svg>
                                <p class="testimonials__content">
                                    Uwielbiam prostotę tej aplikacji. Wszystko
                                    jest jasno wyjaśnione – nawet analiza badań
                                    z laboratorium. Już nie muszę googlować
                                    wyników."
                                </p>
                                <div class="testimonials__line"></div>
                                <div class="testimonials__bottom">
                                    <p class="testimonials__name">
                                        Justyna, instruktorka pilatesu
                                    </p>
                                    <div class="testimonials__rating-container">
                                        <div class="testimonials__stars">
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                        </div>
                                        <span class="testimonials__rating"
                                            >Ocena
                                            <span
                                                class="testimonials__rating testimonials__rating--bold"
                                                >5.0</span
                                            >
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="testimonials__item">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 -960 960 960"
                                    class="testimonials__icon"
                                >
                                    <path
                                        d="m228-240 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T458-480L320-240h-92Zm360 0 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T818-480L680-240h-92ZM320-500q25 0 42.5-17.5T380-560q0-25-17.5-42.5T320-620q-25 0-42.5 17.5T260-560q0 25 17.5 42.5T320-500Zm360 0q25 0 42.5-17.5T740-560q0-25-17.5-42.5T680-620q-25 0-42.5 17.5T620-560q0 25 17.5 42.5T680-500Zm0-60Zm-360 0Z"
                                    />
                                </svg>
                                <p class="testimonials__content">
                                    Trenuję siłowo i testuję różne diety. Ta
                                    aplikacja pozwala mi łatwo sprawdzić, czy
                                    idę w dobrą stronę – bez pisania wszystkiego
                                    w Excelu.
                                </p>
                                <div class="testimonials__line"></div>
                                <div class="testimonials__bottom">
                                    <p class="testimonials__name">
                                        Michał, student fizjoterapii
                                    </p>
                                    <div class="testimonials__rating-container">
                                        <div class="testimonials__stars">
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                        </div>
                                        <span class="testimonials__rating"
                                            >Ocena
                                            <span
                                                class="testimonials__rating testimonials__rating--bold"
                                                >5.0</span
                                            >
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="testimonials__item">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 -960 960 960"
                                    class="testimonials__icon"
                                >
                                    <path
                                        d="m228-240 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T458-480L320-240h-92Zm360 0 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T818-480L680-240h-92ZM320-500q25 0 42.5-17.5T380-560q0-25-17.5-42.5T320-620q-25 0-42.5 17.5T260-560q0 25 17.5 42.5T320-500Zm360 0q25 0 42.5-17.5T740-560q0-25-17.5-42.5T680-620q-25 0-42.5 17.5T620-560q0 25 17.5 42.5T680-500Zm0-60Zm-360 0Z"
                                    />
                                </svg>
                                <p class="testimonials__content">
                                    Polecam aplikację klientom, którzy się gubią
                                    w swoich wynikach. Dostają jasne
                                    podsumowanie i mogą obserwować progres
                                    zdrowotny w czasie.
                                </p>
                                <div class="testimonials__line"></div>
                                <div class="testimonials__bottom">
                                    <p class="testimonials__name">
                                        Agnieszka, dietetyczka kliniczna
                                    </p>
                                    <div class="testimonials__rating-container">
                                        <div class="testimonials__stars">
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                        </div>
                                        <span class="testimonials__rating"
                                            >Ocena
                                            <span
                                                class="testimonials__rating testimonials__rating--bold"
                                                >5.0</span
                                            >
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="swiper-slide">
                            <div class="testimonials__item">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 -960 960 960"
                                    class="testimonials__icon"
                                >
                                    <path
                                        d="m228-240 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T458-480L320-240h-92Zm360 0 92-160q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 42.5T818-480L680-240h-92ZM320-500q25 0 42.5-17.5T380-560q0-25-17.5-42.5T320-620q-25 0-42.5 17.5T260-560q0 25 17.5 42.5T320-500Zm360 0q25 0 42.5-17.5T740-560q0-25-17.5-42.5T680-620q-25 0-42.5 17.5T620-560q0 25 17.5 42.5T680-500Zm0-60Zm-360 0Z"
                                    />
                                </svg>
                                <p class="testimonials__content">
                                    Mam swoich podopiecznych, którzy przesyłają
                                    mi screeny z raportów z tej apki. Jestem pod
                                    wrażeniem – prosto, konkretnie i bez lania
                                    wody.
                                </p>
                                <div class="testimonials__line"></div>
                                <div class="testimonials__bottom">
                                    <p class="testimonials__name">
                                        Kuba, trener crossfitu
                                    </p>
                                    <div class="testimonials__rating-container">
                                        <div class="testimonials__stars">
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                            <i
                                                class="fa-solid fa-star testimonials__star"
                                            ></i>
                                        </div>
                                        <span class="testimonials__rating"
                                            >Ocena
                                            <span
                                                class="testimonials__rating testimonials__rating--bold"
                                                >5.0</span
                                            >
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- If we need pagination -->
                    <div class="swiper-pagination"></div>
                </div>
                <p class="testimonials__text">
                    Przedstawione opinie mają charakter przykładowy i zostały
                    wygenerowane na potrzeby prezentacji aplikacji w ramach
                    projektu portfolio.
                </p>
            </div>
        </section>

        <footer id="contact" class="footer">
            <div class="footer__container">
                <div class="footer__column">
                    <img src="/img/logo_1.png" alt="" class="footer__logo" />
                    <p class="footer__text">
                        Zadbaj o zdrowie dzięki analizie wyników badań, danych
                        zdrowotnych i inteligentnym rekomendacjom AI.
                    </p>
                </div>
                <div class="footer__column">
                    <h6 class="footer__heading">Szybki dostęp</h6>
                    <ul class="footer__menu">
                        <li class="footer__option">
                            <a href="#start" class="footer__link">Start</a>
                        </li>
                        <li class="footer__option">
                            <a href="#about" class="footer__link"
                                >O aplikacji</a
                            >
                        </li>
                        <li class="footer__option">
                            <a href="#faq" class="footer__link">FAQ</a>
                        </li>
                        <li class="footer__option">
                            <a href="#testimonials" class="footer__link"
                                >Opinie</a
                            >
                        </li>
                        <li class="footer__option">
                            <a
                                href="mailto:kontakt@mediary.pl"
                                class="footer__link"
                                >Kontakt</a
                            >
                        </li>
                    </ul>
                </div>
                <div class="footer__column">
                    <h6 class="footer__heading">Informacje</h6>
                    <ul class="footer__menu">
                        <li class="footer__option">
                            <a
                                target="_blank"
                                href="https://platform.openai.com/docs/overview"
                                class="footer__link"
                                >API OpenAI</a
                            >
                        </li>
                        <li class="footer__option">
                            <a
                                target="_blank"
                                href="https://github.com/Jakub017/Healthcare-AI"
                                class="footer__link"
                                >Repozytorium Github</a
                            >
                        </li>
                    </ul>
                </div>

                <div class="footer__column">
                    <h6 class="footer__heading">Kontakt</h6>
                    <ul class="footer__menu">
                        <li class="footer__option">
                            <a
                                href="mailto:kontakt@mediary.pl"
                                class="footer__link"
                                >kontakt@mediary.pl</a
                            >
                        </li>
                        <li class="footer__option">
                            <a
                                href="mailto:kontakt@lipinskijakub.pl"
                                class="footer__link"
                                >kontakt@lipinskijakub.pl</a
                            >
                        </li>
                    </ul>
                </div>
            </div>
            <div class="footer__bottom">
                <div class="footer__bottom-container">
                    <p class="footer__text">
                        Projekt i realizacja:
                        <a
                            class="footer__link footer__link--colored"
                            target="_blank"
                            href="https://lipinskijakub.pl"
                            >Jakub Lipiński</a
                        >
                    </p>
                    <div class="footer__links">
                        <a
                            target="_blank"
                            href="https://www.linkedin.com/in/jakub-lipinski/"
                            class="footer__social"
                            ><i class="fa-brands fa-linkedin"></i
                        ></a>
                        <a
                            target="_blank"
                            href="https://github.com/Jakub017"
                            class="footer__social"
                            ><i class="fa-brands fa-github"></i
                        ></a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
