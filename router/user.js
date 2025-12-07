const express = require("express");
const verifyToken = require("../middlewares/authenticated");
const UserController = require("../controllers/user");

const api = express.Router();

api.get("/user", verifyToken, UserController.getUser);
api.put("/user", verifyToken, UserController.updateUser);

module.exports = api;
