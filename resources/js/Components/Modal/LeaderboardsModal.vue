<template>
  <BaseModal>
    <header>
      <h3>Classements</h3>
    </header>
    <div class="leaderboards__wrapper">
      <div class="leaderboards__selection">
        <p>Choix du classement :</p>
        <div class="leaderboards__dropdown">
          <select>
            <option
              class="option"
              selected
            >
              Elo
            </option>
            <option class="option">
              Niveau
            </option>
            <option class="option">
              Victoires
            </option>
          </select>
          <svg
            viewBox="0 0 16 17"
          >
            <path
              d="M8 12.75C7.80867 12.75 7.62308 12.7175 7.44323 12.6526C7.26338 12.5878 7.11414 12.5013 6.99552 12.3931L0.394618 6.42366C0.131539 6.18575 0 5.88295 0 5.51526C0 5.14758 0.131539 4.84478 0.394618 4.60687C0.657697 4.36896 0.992526 4.25 1.3991 4.25C1.80568 4.25 2.14051 4.36896 2.40359 4.60687L8 9.66793L13.5964 4.60687C13.8595 4.36896 14.1943 4.25 14.6009 4.25C15.0075 4.25 15.3423 4.36896 15.6054 4.60687C15.8685 4.84478 16 5.14758 16 5.51526C16 5.88295 15.8685 6.18575 15.6054 6.42366L9.00448 12.3931C8.86099 12.5229 8.70553 12.615 8.53812 12.6695C8.3707 12.724 8.19133 12.7508 8 12.75Z"
              fill="currentColor"
            />
          </svg>
        </div>
      </div>
      <div class="leaderboards__top-three">
        <div class="leaderboards__second">
          <img
            :src="userStore.avatar"
            alt=""
          >
          <div class="leaderboards__user-presentation">
            <div class="bold">
              2
            </div>
            <p>JohnDoe</p>
            <p class="bold">
              5010
            </p>
          </div>
        </div>
        <div class="leaderboards__first">
          <img
            :src="userStore.avatar"
            alt=""
          >
          <div class="leaderboards__user-presentation">
            <div class="bold">
              1
            </div>
            <p>Unity</p>
            <p class="bold">
              10000
            </p>
          </div>
        </div>
        <div class="leaderboards__third">
          <img
            :src="userStore.avatar"
            alt=""
          >
          <div class="leaderboards__user-presentation">
            <div class="bold">
              3
            </div>
            <p>JaneDoe</p>
            <p class="bold">
              4980
            </p>
          </div>
        </div>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import BaseModal from "./BaseModal.vue";
import { useStore } from "../../stores/user.js";
const userStore = useStore();
</script>
