// data-tooltip 属性を持つ要素に、ホバー(PC)またはタップ(スマホ)で吹き出しを表示する
// 吹き出し本体は layouts/app.blade.php の #tooltip。position: fixed なので overflow の枠で切れない
const MARGIN = 8; // 要素・画面端との間隔(px)

let current = null;

function show(target) {
    const tooltip = document.getElementById('tooltip');
    const text = target.dataset.tooltip;
    if (!tooltip || !text) return;

    tooltip.textContent = text;
    tooltip.classList.remove('hidden');
    current = target;

    // 要素の上に中央揃えで出す。上に入らなければ下に出す
    const rect = target.getBoundingClientRect();
    const tip = tooltip.getBoundingClientRect();
    let top = rect.top - tip.height - MARGIN;
    if (top < MARGIN) top = rect.bottom + MARGIN;
    let left = rect.left + rect.width / 2 - tip.width / 2;
    left = Math.max(MARGIN, Math.min(left, window.innerWidth - tip.width - MARGIN));

    tooltip.style.top = `${top}px`;
    tooltip.style.left = `${left}px`;
}

function hide() {
    const tooltip = document.getElementById('tooltip');
    if (tooltip) tooltip.classList.add('hidden');
    current = null;
}

document.addEventListener('mouseover', (e) => {
    const target = e.target.closest('[data-tooltip]');
    if (target && target !== current) show(target);
});

document.addEventListener('mouseout', (e) => {
    if (!current) return;
    if (current.contains(e.relatedTarget)) return;
    if (e.target.closest('[data-tooltip]') === current) hide();
});

// タップ: 対象要素なら表示、それ以外の場所なら閉じる
// (スマホのタップは mouseover → click の順に発火するため、対象要素のclickでは閉じない)
document.addEventListener('click', (e) => {
    const target = e.target.closest('[data-tooltip]');
    if (target) {
        show(target);
    } else {
        hide();
    }
});

window.addEventListener('scroll', hide, { passive: true });
window.addEventListener('resize', hide);
