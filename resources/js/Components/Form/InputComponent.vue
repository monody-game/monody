<template>
  <div
    class="auth-page__form-group"
    :data-is-invalid="props.errored"
  >
    <label :for="props.name">
      {{ props.label }}
      <span
        v-if="props.labelNote"
        class="auth-page__input-notice"
      >
        ({{ props.labelNote }})
      </span>
      <NoticeComponent
        v-if="props.type === 'email'"
        title="Pourquoi dois-je donner cette information ?"
      >
        Votre email nous est utile lorsque vous perdez votre mot de passe. C’est également un moyen d’identification (connection, connection de votre compte Discord à Monody). Veillez à rentrer une adresse mail valide, vous devrez la vérifier.
      </NoticeComponent>
    </label>
    <input
      :id="props.name"
      v-model="content"
      :type="props.type"
      :name="props.name"
      @input.passive="$emit('model', content)"
    >
    <VisibilityToggle
      v-if="props.type === 'password'"
      class="auth-page__show-password"
      :field="props.name"
    />
    <svg
      v-if="props.errored"
      class="auth-page__error-icon"
    >
      <use href="/sprite.svg#error" />
    </svg>
    <p v-if="props.errored">
      {{ props.error }}
    </p>
  </div>
</template>

<script setup>
import { ref } from "vue";
import VisibilityToggle from "./VisibilityToggle.vue";
import NoticeComponent from "../NoticeComponent.vue";

const props = defineProps({
	name: String,
	type: {
		required: true,
		type: String,
		validator(value) {
			return ["text", "password", "email"].includes(value);
		}
	},
	errored: {
		default: false,
		type: Boolean
	},
	error: String,
	label: String,
	labelNote: String,
	value: {
		default: "",
		type: String,
		required: false
	}
});

defineEmits(["model"]);

const content = ref(props.value);
</script>
