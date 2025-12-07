const express = require("express");
const AuthController = require("../controllers/auth");
const verifyToken = require("../middlewares/authenticated");
const UserController = require("../controllers/user");

const api = express.Router();

api.post("/auth/register", AuthController.register);
api.post("/auth/login", AuthController.login);

api.get("/user", verifyToken, UserController.getUser);
api.put("/user", verifyToken, UserController.updateUser);

api.post("/auth/refreshAccessToken", AuthController.refreshAccessToken);

module.exports = api;
