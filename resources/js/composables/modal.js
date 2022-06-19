export function useModal() {
	return {
		closeModal(store) {
			store.isOpenned = false;
		}
	};
}
