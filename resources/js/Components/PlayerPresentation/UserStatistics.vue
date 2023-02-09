<template>
  <div class="stats__container">
    <div
      class="stats__statistic"
      title="Nombre de victoires"
    >
      <svg>
        <use href="/sprite.svg#trophy" />
      </svg>
      <p>{{ stats.wins }}</p>
    </div>
    <div
      class="stats__statistic"
      title="Ratio de victoires"
    >
      <svg>
        <use href="/sprite.svg#win_rate" />
      </svg>
      <p>{{ winRate }}</p>
    </div>
    <div
      class="stats__statistic"
      title="Nombre de défaites"
    >
      <svg>
        <use href="/sprite.svg#losses" />
      </svg>
      <p>{{ stats.losses }}</p>
    </div>
    <div
      class="stats__statistic"
      title="Classement"
    >
      <svg>
        <use href="/sprite.svg#ranking" />
      </svg>
      <p>{{ stats.rank }}</p>
    </div>
    <div
      class="stats__statistic"
      title="Série de victoires"
    >
      <svg>
        <use href="/sprite.svg#win_streak" />
      </svg>
      <p>{{ stats.win_streak }}</p>
    </div>
    <div
      class="stats__statistic"
      title="Plus longue série de victoires"
    >
      <svg
        viewBox="0 0 30 35"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M22.5291 14.5782C22.1202 14.0448 21.6224 13.5826 21.1602 13.1204C19.969 12.0537 18.6179 11.2892 17.4801 10.1692C14.8311 7.57355 14.2444 3.28898 15.9334 0C14.2444 0.408901 12.7688 1.33337 11.5066 2.34673C6.90199 6.04462 5.0886 12.5692 7.25755 18.1694C7.32866 18.3472 7.39978 18.525 7.39978 18.7561C7.39978 19.1472 7.1331 19.5028 6.77754 19.645C6.36864 19.8228 5.94196 19.7161 5.60417 19.4317C5.50267 19.3477 5.41818 19.2451 5.35528 19.1294C3.34633 16.5871 3.02632 12.9426 4.37747 10.027C1.4085 12.4448 -0.209327 16.5338 0.0217904 20.3917C0.12846 21.2806 0.23513 22.1695 0.537361 23.0584C0.786257 24.1251 1.26627 25.1918 1.79962 26.1341C3.71967 29.2097 7.04421 31.4142 10.6176 31.8587C14.4222 32.3387 18.4934 31.6453 21.4091 29.0142C24.6625 26.063 25.8003 21.3339 24.1291 17.2805L23.898 16.8183C23.5247 16.0005 22.5291 14.5782 22.5291 14.5782ZM16.9112 25.7785C16.4134 26.2052 15.5956 26.6674 14.9555 26.8452C12.9644 27.5563 10.9732 26.5608 9.79985 25.3874C11.9155 24.8896 13.1777 23.3251 13.5511 21.7428C13.8533 20.3206 13.2844 19.1472 13.0533 17.7783C12.8399 16.4627 12.8755 15.3427 13.3555 14.116C13.6933 14.7915 14.0489 15.4671 14.4755 16.0005C15.8445 17.7783 17.9956 18.5605 18.4579 20.9784C18.529 21.2273 18.5645 21.4762 18.5645 21.7428C18.6179 23.2007 17.9779 24.8007 16.9112 25.7785Z"
          fill="currentColor"
        />
        <rect
          x="10"
          y="15"
          width="19"
          height="19"
          rx="9.5"
          fill="var(--light-background)"
          stroke="var(--dark-background)"
          stroke-width="2"
        />
        <path
          d="M22.9425 22.967L19.5 19.8944L16.0575 22.967L15 22.0165L19.5 18L24 22.0165L22.9425 22.967ZM22.9425 26.9835L19.5 23.9109L16.0575 26.9835L15 26.033L19.5 22.0165L24 26.033L22.9425 26.9835ZM22.9425 31L19.5 27.9274L16.0575 31L15 30.0494L19.5 26.033L24 30.0494L22.9425 31Z"
          fill="var(--dark-background)"
        />
      </svg>
      <p>{{ stats.longest_streak }}</p>
    </div>
  </div>
</template>

<script setup>
const apiStats = (await window.JSONFetch("/stats")).data;

const stats = {
	wins: "N/A",
	losses: "N/A",
	rank: "N/A",
	win_streak: "N/A",
	longest_streak: "N/A"
};
let winRate = "N/A";

for (const stat in apiStats) {
	stats[stat] = apiStats[stat];
}

if (stats.wins !== "N/A" && stats.losses !== "N/A") {
	if (stats.losses === 0) {
		winRate = stats.wins;
	} else {
		winRate = stats.wins / stats.losses;

		if (!Number.isInteger(winRate)) {
			winRate = winRate.toFixed(2);
		}
	}
}
</script>
