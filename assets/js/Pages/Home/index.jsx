import { Button } from "@mui/material";
import React from "react";

export default function Home() {
  return (
    <div className="container">
      <h1>Welcome to your new Symfony app!</h1>
      <Button variant="contained">Contained</Button>
      <p>
        Edit
        <code>templates/home/index.html.twig</code>
        to get started.
      </p>
    </div>
  );
}
