export default class NightTimeHandler {
  switchBackround () {
    const list = document.querySelector(".game-page__container").classList;
    if (list.contains("day")) {
      list.remove("day");
    }
    list.add("night");
  }
}