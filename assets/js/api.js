const API_BASE = "http://localhost/projet-reseau-social/api";

async function apiRequest(
  endpoint,
  method = "GET",
  body = null,
  isFormData = false,
) {
  const token = sessionStorage.getItem("token");

  const headers = {};
  if (token) {
    headers["Authorization"] = "Bearer " + token;
  }

  const options = { method, headers };

  if (body) {
    if (isFormData) {
      options.body = body;
    } else {
      headers["Content-Type"] = "application/json";
      options.body = JSON.stringify(body);
    }
  }

  const response = await fetch(`${API_BASE}/${endpoint}`, options);
  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.error || "Une erreur est survenue");
  }

  return data;
}
