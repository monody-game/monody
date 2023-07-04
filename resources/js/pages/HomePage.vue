<template>
	<div v-once class="home-page">
		<header class="home-page__header">
			<router-link :to="{ name: 'home_page' }"> Comment jouer </router-link>
			<router-link :to="{ name: 'login' }" class="btn large btn-primary">
				Se connecter
			</router-link>
		</header>
		<div class="home-page__main">
			<router-link :to="{ name: 'play' }" class="home-page__link">
				<div class="home-page__title">
					<p>Mon</p>
					<svg class="home-page__logo">
						<use href="/sprite.svg#monody" />
					</svg>
					<p>dy</p>
				</div>
				<p class="home-page__play-text">Jouer</p>
			</router-link>
		</div>
		<Footer />
	</div>
</template>

<script setup>
import Footer from "../Components/FooterComponent.vue";
</script>
