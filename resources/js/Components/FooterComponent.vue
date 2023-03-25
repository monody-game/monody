<template>
  <footer
    v-once
    class="footer__main"
  >
    <p>&copy; Monody {{ (new Date()).getFullYear() }} — Tous droits réservés.</p>
    <a
      href="https://www.instagram.com/monody_game"
      target="_blank"
    >
      <svg>
        <use href="/sprite.svg#instagram" />
      </svg>
    </a>
    <a
      href="https://discord.gg/FxeRt58xZt"
      target="_blank"
    >
      <svg>
        <use href="/sprite.svg#discord" />
      </svg>
    </a>
  </footer>
</template>

<script setup>
</script>
