<template>
	<footer v-once class="footer__main">
		<p>
			{{ $t("footer.copyright", [new Date().getFullYear()]) }}
		</p>
		<div class="footer__socials">
			<a
				href="https://www.instagram.com/monody_game"
				target="_blank"
				aria-label="Instagram de Monody"
			>
				<svg role="img">
					<use href="/sprite.svg#instagram" />
				</svg>
			</a>
			<a
				href="https://discord.gg/FxeRt58xZt"
				target="_blank"
				aria-label="Discord de Monody"
			>
				<svg role="img">
					<use href="/sprite.svg#discord" />
				</svg>
			</a>
		</div>
	</footer>
</template>

<script setup></script>
