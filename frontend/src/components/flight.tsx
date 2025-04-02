import React from "react";
import "./stylesF.css";

const Flight: React.FC = () => {
    return (
        <div className="container">
            <h1>Encuentra tu próxima estancia</h1>
            <p>Busca ofertas en hoteles, casas y mucho más...</p>
            <div className="search-bar">
                <input type="text" placeholder="¿A dónde vas?" className="search-input" />
                <input type="date" className="date-picker" />
                <input type="date" className="date-picker" />
                <select className="guest-selector">
                    <option>2 adultos · 0 niños · 1 habitación</option>
                </select>
                <button className="search-button">Buscar</button>
            </div>
        </div>
    );
};

export default Flight;
