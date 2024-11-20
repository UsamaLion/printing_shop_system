
-- Create database
CREATE DATABASE IF NOT EXISTS printing_shop_db;
USE printing_shop_db;

-- Create Users table
CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    RoleID INT NOT NULL,
    ContactDetails TEXT
);

-- Create Roles table
CREATE TABLE Roles (
    RoleID INT AUTO_INCREMENT PRIMARY KEY,
    RoleName VARCHAR(50) NOT NULL
);

-- Create Permissions table
CREATE TABLE Permissions (
    PermissionID INT AUTO_INCREMENT PRIMARY KEY,
    PermissionName VARCHAR(100) NOT NULL
);

-- Create Clients table
CREATE TABLE Clients (
    ClientID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100),
    Address TEXT,
    PrimaryMobile VARCHAR(20) NOT NULL,
    SecondaryMobile VARCHAR(20)
);

-- Create JobTypes table
CREATE TABLE JobTypes (
    JobTypeID INT AUTO_INCREMENT PRIMARY KEY,
    JobTypeName VARCHAR(50) NOT NULL,
    Fields JSON
);

-- Create JobStatus table
CREATE TABLE JobStatus (
    StatusID INT AUTO_INCREMENT PRIMARY KEY,
    StatusName VARCHAR(50) NOT NULL
);

-- Create Jobs table
CREATE TABLE Jobs (
    JobID INT AUTO_INCREMENT PRIMARY KEY,
    JobTypeID INT NOT NULL,
    ClientID INT NOT NULL,
    DesignerID INT NOT NULL,
    PrintingPressID INT,
    StatusID INT NOT NULL,
    Rate DECIMAL(10, 2) NOT NULL,
    Description TEXT,
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    CompletedDate DATETIME,
    PaymentStatus ENUM('Pending', 'Received') DEFAULT 'Pending',
    CustomFields JSON,
    FOREIGN KEY (JobTypeID) REFERENCES JobTypes(JobTypeID),
    FOREIGN KEY (ClientID) REFERENCES Clients(ClientID),
    FOREIGN KEY (DesignerID) REFERENCES Users(UserID),
    FOREIGN KEY (PrintingPressID) REFERENCES Users(UserID),
    FOREIGN KEY (StatusID) REFERENCES JobStatus(StatusID)
);

-- Create JobHistory table
CREATE TABLE JobHistory (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    JobID INT NOT NULL,
    StatusID INT NOT NULL,
    ChangedBy INT NOT NULL,
    ChangedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    Notes TEXT,
    FOREIGN KEY (JobID) REFERENCES Jobs(JobID),
    FOREIGN KEY (StatusID) REFERENCES JobStatus(StatusID),
    FOREIGN KEY (ChangedBy) REFERENCES Users(UserID)
);

-- Create DesignFiles table
CREATE TABLE DesignFiles (
    FileID INT AUTO_INCREMENT PRIMARY KEY,
    JobID INT NOT NULL,
    FilePath VARCHAR(255) NOT NULL,
    UploadedBy INT NOT NULL,
    UploadedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (JobID) REFERENCES Jobs(JobID),
    FOREIGN KEY (UploadedBy) REFERENCES Users(UserID)
);

-- Create Notifications table
CREATE TABLE Notifications (
    NotificationID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    Message TEXT NOT NULL,
    IsRead BOOLEAN DEFAULT FALSE,
    CreatedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- Insert default roles
INSERT INTO Roles (RoleName) VALUES ('Admin'), ('Designer'), ('Printing Press');

-- Insert default job statuses
INSERT INTO JobStatus (StatusName) VALUES 
('Working'), ('Design Ready'), ('Sent for Printing'), ('Printing In Progress'), ('Completed'), ('Revision Needed');

-- Insert default job types
INSERT INTO JobTypes (JobTypeName, Fields) VALUES 
('Plates', '{"Quantity": "int", "Color": "string", "Size": "string", "Rate": "decimal", "Description": "text"}'),
('Color Print', '{"Quantity": "int", "PaperType": "string", "Size": "string", "Color": "string", "Rate": "decimal", "Description": "text"}'),
('Pana Flex', '{"Quantity": "int", "Size": "string", "Media": "string", "Folding": "boolean", "Ring": "boolean", "Rate": "decimal", "Description": "text"}'),
('Film', '{"Quantity": "int", "Color": "string", "Size": "string", "Rate": "decimal", "Description": "text"}'),
('Offset', '{"Quantity": "int", "Color": "string", "Size": "string", "Lamination": "boolean", "Binding": "boolean", "Plates": "boolean", "MachineType": "string", "DyeCutting": "boolean", "Rate": "decimal", "Description": "text"}'),
('Wedding Card', '{"Firm": "string", "Quantity": "int", "CardColor": "string", "EventType": "enum:Mehndi,Baraat,Walima", "CardNo": "string", "PrintingColor": "string", "Rate": "decimal", "Description": "text"}'),
('Other Jobs', '{"Quantity": "int", "Description": "text"}');

-- Insert a default admin user (password: admin123)
INSERT INTO Users (Name, Email, Password, RoleID, ContactDetails) VALUES 
('Admin', 'admin@printingshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '{"phone": "1234567890"}');
