﻿using System;
using System.Collections.Generic;
using System.Configuration;
using System.Diagnostics;
using System.IO;
using System.Net;
using System.ServiceProcess;
using System.Text;
using System.Threading;
using System.Web;

// ref: https://docs.microsoft.com/en-us/dotnet/framework/windows-services/walkthrough-creating-a-windows-service-application-in-the-component-designer

namespace ParentSteamService
{
    public partial class SteamClientService2 : ServiceBase
    {
        // note: change access permissions on the hosts file so this service can edit the file
        private const string FILE_HOSTS = @"C:\Windows\System32\drivers\etc\hosts";

        private const string HEADER_HOSTS = @"# Copyright (c) 1993-2009 Microsoft Corp.
#
# This is a sample HOSTS file used by Microsoft TCP/IP for Windows.
#
# This file contains the mappings of IP addresses to host names. Each
# entry should be kept on an individual line. The IP address should
# be placed in the first column followed by the corresponding host name.
# The IP address and the host name should be separated by at least one
# space.
#
# Additionally, comments (such as these) may be inserted on individual
# lines or following the machine name denoted by a '#' symbol.
#
# For example:
#
#      102.54.94.97     rhino.acme.com          # source server
#       38.25.63.10     x.acme.com              # x client host

# localhost name resolution is handled within DNS itself.
#	127.0.0.1       localhost
#	::1             localhost
";

        private Thread _mThread = null;
        private bool _mWaitForExit = true;
        private string _mLastContent = null;

        public SteamClientService2()
        {
            InitializeComponent();
        }

        static string GetSetting(string key, string defValue)
        {
            try
            {
                var configFile = ConfigurationManager.OpenExeConfiguration(ConfigurationUserLevel.None);
                var settings = configFile.AppSettings.Settings;
                if (settings[key] != null)
                {
                    return settings[key].Value;
                }
            }
            catch (ConfigurationErrorsException)
            {
                Console.WriteLine("Error reading app settings");
            }
            return defValue;
        }

        static void SetSetting(string key, string value)
        {
            try
            {
                var configFile = ConfigurationManager.OpenExeConfiguration(ConfigurationUserLevel.None);
                var settings = configFile.AppSettings.Settings;
                if (settings[key] == null)
                {
                    settings.Add(key, value);
                }
                else
                {
                    settings[key].Value = value;
                }
                configFile.Save(ConfigurationSaveMode.Modified);
                ConfigurationManager.RefreshSection(configFile.AppSettings.SectionInformation.Name);
            }
            catch (ConfigurationErrorsException)
            {
                Console.WriteLine("Error writing app settings");
            }
        }

        protected override void OnStart(string[] args)
        {
            _mWaitForExit = true;
            ThreadStart ts = new ThreadStart(ThreadWorker);
            _mThread = new Thread(ts);
            _mThread.Start();
        }

        protected override void OnStop()
        {
            _mWaitForExit = false;
        }

        private void SafeSleep(uint milliseconds)
        {
            uint count = 0;
            while (count < milliseconds)
            {
                if (!_mWaitForExit)
                {
                    return;
                }
                Thread.Sleep(1);
                ++count;
            }
        }

        private string GetProcessName(Process process)
        {
            string processName = process.ProcessName;
            try
            {
                processName = process.MainModule.FileName;
            }
            catch
            {

            }
            return processName;
        }

        private void PostProcesses()
        {
            try
            {
                SortedList<string, bool> processList = new SortedList<string, bool>();
                Process[] processes = Process.GetProcesses();                
                foreach (Process p in processes)
                {
                    string processName = GetProcessName(p);
                    if (string.IsNullOrEmpty(processName))
                    {
                        continue;
                    }
                    processName = processName.ToLower().Trim();
                    if (!processList.ContainsKey(processName))
                    {
                        processList.Add(processName, false);
                    }
                }

                // request the get URI to post running processes
                try
                {
                    string url = ConfigurationSettings.AppSettings["PostUri"];
                    string data = "computer=" + HttpUtility.UrlEncode(Environment.MachineName).ToLower();
                    data += "&data=";
                    foreach (KeyValuePair<string, bool> kvp in processList)
                    {
                        string process = kvp.Key;
                        data += HttpUtility.UrlEncode(process + Environment.NewLine);
                    }
                    byte[] byteArray = Encoding.UTF8.GetBytes(data);
                    Uri uri = new Uri(url);
                    HttpWebRequest request = (HttpWebRequest)HttpWebRequest.Create(uri);
                    request.Timeout = 1000;
                    request.Method = "POST";
                    request.ContentType = "application/x-www-form-urlencoded";
                    request.ContentLength = byteArray.Length;
                    using (Stream webpageStream = request.GetRequestStream())
                    {
                        webpageStream.Write(byteArray, 0, byteArray.Length);
                    }
                    HttpWebResponse response = (HttpWebResponse)request.GetResponse();
                    if (response.StatusCode == HttpStatusCode.OK)
                    {
                    }
                    response.Close();
                }
                catch (Exception ex)
                {
                    Console.Error.WriteLine("Failed to post exception: {0}", ex);
                }
            }
            catch (Exception)
            {

            }
        }

        private void EndProcesses(string blob)
        {
            try
            {
                Process[] processes = Process.GetProcesses();
                if (string.IsNullOrEmpty(blob))
                {
                    return;
                }
                string[] lines = blob.Split("\n".ToCharArray());
                foreach (string line in lines)
                {
                    string l = line.Trim();
                    if (string.IsNullOrEmpty(l))
                    {
                        continue;
                    }
                    // skip any commented out lines
                    if (l.StartsWith("#"))
                    {
                        continue;
                    }
                    foreach (Process p in processes)
                    {
                        string processName = GetProcessName(p);
                        if (string.IsNullOrEmpty(processName))
                        {
                            continue;
                        }
                        processName = processName.ToLower().Trim();
                        // stop any processes with the same name
                        if (processName == l.ToLower())
                        {
                            try
                            {
                                p.Kill();
                            }
                            catch (Exception)
                            {

                            }
                        }
                    }
                }
            }
            catch (Exception)
            {

            }
        }

        private const string KEY_CACHE_SETTING_END = "KEY_CACHE_SETTING_END";

        private void ThreadWorker()
        {
            while (_mWaitForExit)
            {
                SafeSleep(3000);
                if (!_mWaitForExit)
                {
                    break;
                }

                // request the get URI to post running processes
                try
                {
                    string url = ConfigurationSettings.AppSettings["GetUri"];
                    string query = "?computer=" + HttpUtility.UrlEncode(Environment.MachineName).ToLower();
                    Uri uri = new Uri(url + query);
                    HttpWebRequest request = (HttpWebRequest)HttpWebRequest.Create(uri);
                    request.Timeout = 1000;
                    HttpWebResponse response = (HttpWebResponse)request.GetResponse();
                    if (response.StatusCode == HttpStatusCode.OK)
                    {
                        using (StreamReader sr = new StreamReader(response.GetResponseStream()))
                        {
                            if (sr.ReadToEnd().Trim().ToLower() == "yes")
                            {
                                PostProcesses();
                            }
                        }
                    }
                    response.Close();
                }
                catch
                {

                }

                // request the end URI to get processes that should end
                string processes = null;
                try
                {
                    string url = ConfigurationSettings.AppSettings["EndUri"];
                    string query = "?computer=" + HttpUtility.UrlEncode(Environment.MachineName).ToLower();
                    Uri uri = new Uri(url + query);
                    HttpWebRequest request = (HttpWebRequest)HttpWebRequest.Create(uri);
                    request.Timeout = 1000;
                    HttpWebResponse response = (HttpWebResponse)request.GetResponse();
                    if (response.StatusCode == HttpStatusCode.OK)
                    {
                        using (StreamReader sr = new StreamReader(response.GetResponseStream()))
                        {
                            processes = sr.ReadToEnd();
                            if (processes != null)
                            {
                                // Save the processes in the cache in case of airplane mode
                                SetSetting(KEY_CACHE_SETTING_END, processes);
                            }
                        }
                    }
                    response.Close();
                }
                catch
                {
                }

                try
                {
                    if (null == processes)
                    {
                        processes = GetSetting(KEY_CACHE_SETTING_END, string.Empty);
                    }
                }
                catch
                {
                }

                try
                {
                    if (!string.IsNullOrEmpty(processes))
                    {
                        EndProcesses(processes);
                    }
                }
                catch
                {

                }

                // request the hosts URI to get hosts changes
                string content = null;
                try
                {
                    string url = ConfigurationSettings.AppSettings["HostsUri"];
                    string query = "?computer=" + HttpUtility.UrlEncode(Environment.MachineName).ToLower();
                    Uri uri = new Uri(url + query);
                    HttpWebRequest request = (HttpWebRequest)HttpWebRequest.Create(uri);
                    request.Timeout = 1000;
                    HttpWebResponse response = (HttpWebResponse)request.GetResponse();
                    if (response.StatusCode == HttpStatusCode.OK)
                    {
                        using (StreamReader sr = new StreamReader(response.GetResponseStream()))
                        {
                            content = sr.ReadToEnd();
                        }
                    }
                    response.Close();
                }
                catch
                {

                }

                // no need to write updates that haven't changed
                if (_mLastContent != content)
                {
                    // watch for changes
                    _mLastContent = content;

                    // write the hosts changes
                    try
                    {
                        if (!string.IsNullOrEmpty(content))
                        {
                            using (FileStream fs = File.Open(FILE_HOSTS, FileMode.OpenOrCreate, FileAccess.Write, FileShare.ReadWrite))
                            {
                                using (StreamWriter sw = new StreamWriter(fs))
                                {
                                    string contents = HEADER_HOSTS;
                                    sw.Write(contents);
                                    sw.WriteLine();
                                    sw.WriteLine("{0}", content);
                                    sw.Flush();
                                }
                            }
                        }
                    }
                    catch
                    {

                    }
                }

                // check if reboot is needed

                try
                {
                    string url = ConfigurationSettings.AppSettings["RebootUri"];
                    string query = "?computer=" + HttpUtility.UrlEncode(Environment.MachineName).ToLower();
                    Uri uri = new Uri(url + query);
                    HttpWebRequest request = (HttpWebRequest)HttpWebRequest.Create(uri);
                    request.Timeout = 1000;
                    HttpWebResponse response = (HttpWebResponse)request.GetResponse();
                    if (response.StatusCode == HttpStatusCode.OK)
                    {
                        using (StreamReader sr = new StreamReader(response.GetResponseStream()))
                        {
                            if (sr.ReadToEnd().Trim().ToLower() == "yes")
                            {
                                System.Diagnostics.Process.Start("shutdown.exe", "-r -t 0");
                            }
                        }
                    }
                    response.Close();
                }
                catch
                {

                }
            }
        }
    }
}
