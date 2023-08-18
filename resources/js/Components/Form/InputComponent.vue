<template>
	<div
		class="auth-page__form-group"
		:data-is-invalid="props.errored"
		:style="props.type === 'email' && props.note ? 'position: static;' : ''"
	>
		<label
			:for="props.name"
			@mouseover="shown = true"
			@mouseout="shown = false"
		>
			{{ props.label }}
			{{ props.required === false ? "(" + $t("auth.optional") + ")" : "" }}
			<span v-if="props.labelNote" class="auth-page__input-notice">
				({{ props.labelNote
				}}<span
					v-text="
						props.required === true
							? ', ' + $t('auth.mandatory')
							: ', ' + $t('auth.optional')
					"
				/>)
			</span>
			<NoticeComponent
				v-if="props.type === 'email' && props.note"
				:title="$t('auth.email_popup.title')"
				:shown="shown"
			>
				{{ $t("auth.email_popup.content") }}
			</NoticeComponent>
		</label>
		<input
			:id="props.name"
			v-model="content"
			:type="props.type"
			:name="props.name"
			@input.passive="$emit('model', content)"
		/>
		<VisibilityToggle
			v-if="props.type === 'password'"
			class="auth-page__show-password"
			:field="props.name"
		/>
		<svg v-if="props.errored" class="auth-page__error-icon">
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
	name: {
		required: true,
		type: String,
	},
	type: {
		required: true,
		type: String,
		validator(value) {
			return ["text", "password", "email"].includes(value);
		},
	},
	errored: {
		default: false,
		type: Boolean,
	},
	error: String,
	label: String,
	labelNote: String,
	value: {
		default: "",
		type: String,
		required: false,
	},
	note: {
		default: true,
		type: Boolean,
		required: false,
	},
	required: {
		default: true,
		type: Boolean,
		required: false,
	},
});

defineEmits(["model"]);

const shown = ref(false);
const content = ref(props.value);
</script>
