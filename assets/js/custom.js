function disableQuantityFields() {
  jQuery(".wc-block-components-quantity-selector__input").attr("disabled", "disabled");
  jQuery(".wc-block-components-quantity-selector__button").attr("disabled", "disabled");
}

// Run once when page fully loads
window.addEventListener("load", function () {
  disableQuantityFields();

  // Also run again whenever WooCommerce updates the cart DOM
  const target = document.body;
  const observer = new MutationObserver(disableQuantityFields);

  observer.observe(target, { childList: true, subtree: true });
});