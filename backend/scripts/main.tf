# This code is compatible with Terraform 4.25.0 and versions that are backwards compatible to 4.25.0.
# For information about validating this Terraform code, see https://developer.hashicorp.com/terraform/tutorials/gcp-get-started/google-cloud-platform-build#format-and-validate-the-configuration

terraform {
  required_providers {
    google = {
      source  = "hashicorp/google"
      version = "4.59.0"
    }
  }
}

provider "google" {
  project = "auto-grader-388203"
  region  = "asia-southeast2"
}

resource "google_compute_instance" "auto-grader-instance" {
  boot_disk {
    auto_delete = true
    device_name = "auto-grader-instance"

    initialize_params {
      image = "projects/debian-cloud/global/images/debian-11-bullseye-v20231212"
      size  = 30
      type  = "pd-ssd"
    }

    mode = "READ_WRITE"
  }

  can_ip_forward      = false
  deletion_protection = false
  enable_display      = true

  labels = {
    app         = "auto-grader"
    goog-ec-src = "vm_add-tf"
  }

  machine_type = "e2-medium"
  name         = "auto-grader-instance"

  network_interface {
    access_config {
      network_tier = "PREMIUM"
    }

    subnetwork = "projects/auto-grader-388203/regions/asia-southeast2/subnetworks/default"
  }

  scheduling {
    automatic_restart   = true
    on_host_maintenance = "MIGRATE"
    preemptible         = false
    provisioning_model  = "STANDARD"
  }

  service_account {
    email  = "568300867435-compute@developer.gserviceaccount.com"
    scopes = ["https://www.googleapis.com/auth/devstorage.read_only", "https://www.googleapis.com/auth/logging.write", "https://www.googleapis.com/auth/monitoring.write", "https://www.googleapis.com/auth/service.management.readonly", "https://www.googleapis.com/auth/servicecontrol", "https://www.googleapis.com/auth/trace.append"]
  }

  shielded_instance_config {
    enable_integrity_monitoring = true
    enable_secure_boot          = false
    enable_vtpm                 = true
  }

  tags = ["http-server", "https-server", "lb-health-check"]
  zone = "asia-southeast2-a"
}
