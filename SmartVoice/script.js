const widgets = [
    { name: "Počasie", iconClass: "fas fa-cloud-sun", equipped: false },
    { name: "Hodiny", iconClass: "fas fa-clock", equipped: false },
    { name: "Kalendár", iconClass: "fas fa-calendar-alt", equipped: false },
    { name: "Spotify", iconClass: "fab fa-spotify", equipped: false },
    { name: "Doorbell", iconClass: "fas fa-door-open", equipped: false }
  ];
  
  const container = document.getElementById("widget-container");
  
  function renderWidgets() {
    container.innerHTML = "";
    widgets.forEach((widget, index) => {
      const card = document.createElement("div");
      card.className = "card" + (widget.equipped ? " equipped" : "");
      card.innerHTML = `
        <div class="imgBox">
          <i class="${widget.iconClass} widget-icon"></i>
        </div>
        <div class="contentBox">
          <h3>${widget.name}</h3>
          <br>
          <a href="#" class="equip-btn" data-index="${index}">
            ${widget.equipped ? "Odstráň" : "Použi"}
          </a>
        </div>
      `;
      container.appendChild(card);
    });
  
    document.querySelectorAll(".equip-btn").forEach(btn => {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        const index = parseInt(this.dataset.index);
        const equippedCount = widgets.filter(w => w.equipped).length;
  
        if (widgets[index].equipped) {
          widgets[index].equipped = false;
        } else if (equippedCount < 4) {
          widgets[index].equipped = true;
        } else {
          alert("Môžeš mať max 4 vybavené widgety!");
        }
  
        renderWidgets();
      });
    });
  }
  
  renderWidgets();
  