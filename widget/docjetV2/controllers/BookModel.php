<?php

function getActiveBooks($leadId) {
  global $db;
  $stmt = $db->query('SELECT * FROM books WHERE lead_id = "'.$leadId.'" AND cancellation = 0');
  return $stmt->fetchAll();
}

function filterBooksBySan($el) {
    global $data;
    return $el["foundation"] == $data['sanid'];
}

function annulBook($card_id, $san_id) {
  global $db;
  $stmt = $db->prepare("UPDATE books SET cancellation = 1 WHERE lead_id = ? and cancellation = 0 and foundation = ?");
  $stmt->execute([$card_id, $san_id]);
  return true;
}

function createBook($card_id, $san_id, $bookingModel) {
  global $db;
  $stmt = $db->prepare("INSERT INTO books (lead_id, foundation, payload) VALUES (?, ?, ?)");
  $stmt->execute([$card_id, $san_id, json_encode($bookingModel)]);
  return $db->lastInsertId();
}

function createBookNotSend($card_id, $san_id, $bookingModel, $type = 'not sended') {
  global $db;
  $stmt = $db->prepare("INSERT INTO books (lead_id, foundation, payload, type) VALUES (?, ?, ?, ?)");
  $stmt->execute([$card_id, $san_id, json_encode($bookingModel), $type]);
  
  return $db->lastInsertId();
}

function changeNotSended($bookId) {
  global $db;
  $stmt = $db->prepare("UPDATE books SET type = ? where id = ?");
  $stmt->execute([' ', $bookId]);
  return true;
}

function changeBook($bookingModel, $bookId) {
  global $db;
  $stmt = $db->prepare("UPDATE books SET payload = ? where id = ?");
  $stmt->execute([json_encode($bookingModel), $bookId]);
  return true;
}
?>