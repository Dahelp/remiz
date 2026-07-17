const burger = document.querySelector(".burger");
const menu = document.querySelector(".menu");

if (burger && menu) {
  burger.addEventListener("click", () => {
    menu.classList.toggle("is-open");
  });
}

document.querySelectorAll(".chips").forEach((group) => {
  group.addEventListener("click", (event) => {
    const chip = event.target.closest(".chip");
    if (!chip) return;
    group.querySelectorAll(".chip").forEach((item) => item.classList.remove("is-active"));
    chip.classList.add("is-active");
  });
});

const calc = document.querySelector("[data-calc]");
if (calc) {
  const price = calc.querySelector("[data-price]");
  calc.addEventListener("click", () => {
    const form = calc.querySelector(".chip.is-active[data-form]")?.dataset.form || "straight";
    const material = calc.querySelector(".chip.is-active[data-material]")?.dataset.material || "ldsp";
    const length = calc.querySelector(".chip.is-active[data-length]")?.dataset.length || "3";
    const base = { straight: 41000, corner: 52000, island: 76000 }[form];
    const materialRate = { ldsp: 1, mdf: 1.35, enamel: 1.72, veneer: 1.95 }[material];
    const total = Math.round(base * materialRate * Number(length) / 1000) * 1000;
    price.textContent = `от ${total.toLocaleString("ru-RU")} ₽`;
  });
}
