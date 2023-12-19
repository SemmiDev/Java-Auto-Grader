# Makefile

# Nama inventory file
INVENTORY = inventory

# Target utama
all: ping

# Run all playbook
all:
	ansible-playbook -i inventory site.yaml

# Target untuk menjalankan perintah Ansible dengan modul ping
ping:
	ansible server_belajar_ansible -m ping

# Target untuk membersihkan hasil eksekusi sebelumnya (opsional)
clean:
	# Tambahkan perintah membersihkan jika diperlukan

# Target untuk menampilkan bantuan
help:
	@echo "Penggunaan:"
	@echo "  make ping       Menjalankan Ansible dengan modul ping"
	@echo "  make clean      Membersihkan hasil eksekusi sebelumnya (opsional)"
	@echo "  make help       Menampilkan pesan bantuan ini"

.PHONY: ping clean help
