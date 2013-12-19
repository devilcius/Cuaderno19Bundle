<?php

include("TCuaderno19.php");

// Cramos el objeto
$cuaderno19 = new T_cuaderno19;

// Configuramos el Presentador
$cuaderno19->configuraPresentador("NIF", "000", "zerodesigners.com", "ENTIDAD", "OFICINA");

// Generamos un Ordenante
$ultOrdenante = $cuaderno19->agregaOrdenante("NIF", "000", "zerodesigners.com", "ENTIDAD", "OFICINA", "DC", "CUENTA");

// A�adimos Recibos al Ordenante
$cuaderno19->agregaRecibo($ultOrdenante, "idcli", "Nombre", "ENTIDAD", "OFICINA", "DC", "CUENTA", 208, "fra 1                         208,00 EUR");
$cuaderno19->agregaRecibo($ultOrdenante, "idcli1", "Nombre1", "ENTIDAD", "OFICINA", "DC", "CUENTA", 208.01, "fra 2                         208,01 EUR");
$cuaderno19->agregaRecibo($ultOrdenante, "idcli2", "Nombre2", "ENTIDAD", "OFICINA", "DC", "CUENTA", 208.20, "fra 3                         208,20 EUR");
$cuaderno19->agregaRecibo($ultOrdenante, "idcli3", "Nombre3", "ENTIDAD", "OFICINA", "DC", "CUENTA", 208.945, "fra 4                         208,95 EUR");

echo "<pre>";
echo $cuaderno19->generaRemesa();
echo "</pre>";
?>