function solicitarViagem(idPassageiro) {
    let partida = document.getElementById("partida").value.trim();
    let destino = document.getElementById("destino").value.trim();

    if (partida === "" || destino === "") {
        document.getElementById("msg").innerHTML = "Preencha todos os campos!";
        return;
    }

    let form = new FormData();
    form.append("id_passageiro", idPassageiro);
    form.append("partida", partida);
    form.append("destino", destino);

    fetch("solicitar_viagem_ajax.php", { method: "POST", body: form })
    .then(r => r.text())
    .then(res => {
        if (res === "OK") {
            window.location.href = "espera_viagem.php";
        } else {
            document.getElementById("msg").innerHTML = "Erro ao solicitar viagem!";
        }
    });
}

// ---------- MOTORISTA ACEITA VIAGEM ----------
function aceitarViagem(idMotorista, idViagem) {
    let form = new FormData();
    form.append("id_motorista", idMotorista);
    form.append("id_viagem", idViagem);

    fetch("aceitar_viagem_ajax.php", { method: "POST", body: form })
    .then(r => r.text())
    .then(res => {
        if (res === "OK") {
            alert("Viagem aceita!");
            window.location.href = "dashboard.php";
        }
    });
}

// ---------- FINALIZAR VIAGEM ----------
function finalizarViagem(idViagem) {
    if (!confirm("Deseja finalizar a viagem?")) return;

    let form = new FormData();
    form.append("id_viagem", idViagem);

    fetch("finalizar.php", { method: "POST", body: form })
    .then(r => r.text())
    .then(res => {
        if (res.includes("OK")) {
            alert("Viagem finalizada!");
            window.location.href = "minhas_viagens.php";
        }
    });
}
