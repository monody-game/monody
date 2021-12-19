export default class DayTimeHandler {
  switchBackround () {
    const list = document.querySelector(".game-page__container").classList;
    if (list.contains("night")) {
      list.remove("night");
    }
    list.add("day");
  }
}