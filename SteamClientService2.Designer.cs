﻿namespace ParentSteamService
{
    partial class SteamClientService2
    {
        /// <summary> 
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Component Designer generated code

        /// <summary> 
        /// Required method for Designer support - do not modify 
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.components = new System.ComponentModel.Container();
            this._mTimer1 = new System.Windows.Forms.Timer(this.components);
            // 
            // _mTimer1
            // 
            this._mTimer1.Interval = 3000;
            this._mTimer1.Tick += new System.EventHandler(this._mTimer1_Tick);
            // 
            // SteamClientService2
            // 
            this.ServiceName = "SteamClientService2";

        }

        #endregion

        private System.Windows.Forms.Timer _mTimer1;
    }
}
