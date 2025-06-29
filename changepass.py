import bcrypt
import mysql.connector

# Koneksi ke database
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="123456",
    database="fuel_tracking"  # ganti sesuai nama DB lo
)

cursor = db.cursor()

# Password baru yang akan digunakan semua user
new_password = "123"
hashed_password = bcrypt.hashpw(new_password.encode('utf-8'), bcrypt.gensalt()).decode('utf-8')

# Ambil semua username
cursor.execute("SELECT username FROM users")
users = cursor.fetchall()

# Update semua user
for (username,) in users:
    cursor.execute(
        "UPDATE users SET password = %s WHERE username = %s",
        (hashed_password, username)
    )
    print(f"Password untuk {username} berhasil diupdate!")

db.commit()
cursor.close()
db.close()

print("✅ Semua password berhasil diganti ke password baru.")
