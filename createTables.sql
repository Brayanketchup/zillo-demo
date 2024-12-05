CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    customer_id INT NOT NULL,
    booking_date DATE NOT NULL,
    FOREIGN KEY (property_id) REFERENCES properties(id),
    FOREIGN KEY (customer_id) REFERENCES Users(user_id)
);


CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loc VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    hometype VARCHAR(100) NOT NULL,
    booked TINYINT(1) NOT NULL DEFAULT 0,
    note TEXT
);

CREATE TABLE 2024F_martbray.Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL
);




-- ////////////////////////////////////

CREATE TABLE appointments (
  appointment_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  property_id INT NOT NULL,
  customer_id INT NOT NULL,
  agent_id INT NOT NULL,
  appointment_date DATE NOT NULL,
  FOREIGN KEY (property_id) REFERENCES properties(id),
  FOREIGN KEY (customer_id) REFERENCES Users(user_id),
  FOREIGN KEY (agent_id) REFERENCES Users(user_id)
);

select * from appointments;



CREATE TABLE bookings (
  booking_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  property_id INT NOT NULL,
  customer_id INT NOT NULL,
  booking_from DATE NOT NULL,
  booking_date DATE NOT NULL,
  status ENUM('booked', 'cancelled', 'completed') NOT NULL DEFAULT 'booked',
  FOREIGN KEY (property_id) REFERENCES properties(id),
  FOREIGN KEY (customer_id) REFERENCES Users(user_id)
);



CREATE TABLE properties (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  loc VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  hometype VARCHAR(100) NOT NULL,
  note TEXT,
  booked TINYINT NOT NULL DEFAULT 0,
  created_by INT NOT NULL,
  purpose ENUM('rent', 'sale', 'both') NOT NULL DEFAULT 'rent',
  FOREIGN KEY (created_by) REFERENCES Users(user_id)
);

CREATE TABLE Users (
  user_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  login VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(100) NOT NULL,
  role ENUM('admin', 'user') NOT NULL
);

    
    
    