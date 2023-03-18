module.exports = {
	extends: [
		"eslint:recommended",
		"plugin:vue/vue3-recommended"
	],
	env: {
		"es6": true
	},
	parserOptions: {
		"ecmaVersion": 2022
	},
	ignorePatterns: ["public/*", "websockets/*"],
	rules: {
		"vue/require-default-prop": 0,
		"quotes": ["error", "double"],
		"arrow-spacing": [
			"warn",
			{
				"before": true,
				"after": true
			}
		],
		"comma-spacing": "error",
		"comma-style": "error",
		"curly": [
			"error",
			"multi-line",
			"consistent"
		],
		"dot-location": [
			"error",
			"property"
		],
		"handle-callback-err": "off",
		"indent": [
			"error",
			"tab"
		],
		"keyword-spacing": "error",
		"max-nested-callbacks": [
			"error",
			{
				"max": 4
			}
		],
		"max-statements-per-line": [
			"error",
			{
				"max": 2
			}
		],
		"no-console": "off",
		"no-empty-function": "error",
		"no-floating-decimal": "error",
		"no-inline-comments": "error",
		"no-lonely-if": "error",
		"no-multi-spaces": "error",
		"no-multiple-empty-lines": [
			"error",
			{
				"max": 2,
				"maxEOF": 1,
				"maxBOF": 0
			}
		],
		"no-shadow": [
			"error",
			{
				"allow": [
					"err",
					"resolve",
					"reject"
				]
			}
		],
		"no-trailing-spaces": [
			"error"
		],
		"no-var": "error",
		"object-curly-spacing": [
			"error",
			"always"
		],
		"prefer-const": "error",
		"semi": [
			"error",
			"always"
		],
		"space-before-blocks": "error",
		"space-in-parens": "error",
		"space-infix-ops": "error",
		"space-unary-ops": "error",
		"spaced-comment": "error",
		"yoda": "error"
	}
};
