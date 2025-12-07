document.getElementById("registerForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const payload = {
    firstname: firstname.value,
    lastname: lastname.value,
    email: email.value,
    password: password.value
  };

  const res = await fetch(`${API}/auth/register`, {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify(payload)
  });

  const data = await res.json();

  if (res.ok) {
    msg.textContent = data.msg;
    msg.className = "success";
  } else {
    msg.textContent = data.msg;
    msg.className = "error";
  }
});
