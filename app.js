const express = require("express");
const cors = require("cors");
const bodyParser = require("body-parser");
const { API_VERSION } = require("./constante");

const userRoutes = require("./router/user");

const path = require("path");

// ⬇️ importa tu router
const authRoutes = require("./router/auth");

const app = express();

app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());

app.use(`/api/${API_VERSION}`, userRoutes);

app.use(express.static(path.join(__dirname, "public")));

app.use(express.static("uploads"));
app.use(cors());

// ⬇️ monta las rutas bajo /api/v1
app.use(`/api/${API_VERSION}`, authRoutes);

module.exports = app;
