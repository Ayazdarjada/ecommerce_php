    </div> <!-- admin-content -->
  </div>
</div>
<script>
function breakRow(btn){
  if(!confirm('Are you sure?')) return false;
  
  btn.classList.add("clicked");
  const id  = btn.dataset.id;
  const url = btn.dataset.url;
  const csrf = btn.dataset.csrf;

  document.body.style.overflowY = "hidden";

  const row = btn.closest("tr");
  const tbody = row.parentElement;
  const rows = Array.from(tbody.children);

  const firstPositions = new Map();
  rows.forEach(r => firstPositions.set(r, r.getBoundingClientRect()));

  /* PARTICLES */
  const rect = row.getBoundingClientRect();
  const dots = 120;
  const colors = ["#38bdf8","#22c55e","#f97316","#e879f9","#facc15","#fb7185"];

  for(let i=0;i<dots;i++){
    const p = document.createElement("div");
    p.className = "particle";
    p.style.left = rect.left + Math.random()*rect.width + "px";
    p.style.top  = rect.top  + Math.random()*rect.height + "px";
    p.style.setProperty("--x", (Math.random()*900 - 450) + "px");
    p.style.setProperty("--y", (Math.random()*700 - 350) + "px");
    p.style.setProperty("--c", colors[Math.floor(Math.random()*colors.length)]);
    document.body.appendChild(p);
    setTimeout(()=>p.remove(),2000);
  }

  row.style.opacity = "0";

  setTimeout(() => {
    row.remove();

    /* SLIDE OTHER ROWS */
    Array.from(tbody.children).forEach(r => {
      const first = firstPositions.get(r);
      const last  = r.getBoundingClientRect();
      if(!first) return;

      const deltaY = first.top - last.top;
      if(deltaY){
        r.style.transform = `translateY(${deltaY}px)`;
        r.style.transition = "transform 0s";
        requestAnimationFrame(()=>{
          r.style.transform = "";
          r.style.transition = "transform 0.6s ease";
        });
      }
    });

    document.body.style.overflowY = "";

    /* 🔥 REAL ACTION (POST) */
    fetch(url, { 
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id=${id}&csrf_token=${csrf}`
    })
    .then(res => {
        if(!res.ok) {
            // The row was already animated away; tell the user why and bring it back
            return res.text().then(t => { alert(t || "Action failed"); location.reload(); });
        }
    })
    .catch(() => { alert("Action failed"); location.reload(); });

  }, 300);

  return false;
}
</script>
</body>
</html>
