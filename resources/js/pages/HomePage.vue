<template>
	<div v-once class="home-page">
		<header class="home-page__header">
			<a
				href="https://docs.monody.fr"
				target="_blank"
			>
				{{ $t("home.howtoplay") }}
				<svg class="link__external">
					<use href="/sprite.svg#external" />
				</svg>
			</a>
			<router-link :to="{ name: 'login' }" class="btn large btn-primary">
				{{ $t("auth.signin") }}
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
				<p class="home-page__play-text">{{ $t("home.play") }}</p>
			</router-link>
		</div>
		<Footer />
	</div>
</template>

<script setup>
import Footer from "../Components/FooterComponent.vue";
</script>
